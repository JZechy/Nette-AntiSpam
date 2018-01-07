<?php

namespace Zet\AntiSpam;

use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class QuestionGenerator
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\AntiSpam
 */
class QuestionGenerator {
	
	/**
	 * @var string
	 */
	private $htmlName;
	
	/**
	 * @var array
	 */
	private $numbers;
	
	/**
	 * @var string
	 */
	private $question;
	
	/**
	 * @var string
	 */
	private $htmlId;
	
	/**
	 * @var array
	 */
	private $operation = [
		"+", "-"
	];
	
	/**
	 * @var Html
	 */
	private $labelPrototype;
	
	/**
	 * @var Html
	 */
	private $inputPrototype;
	
	/**
	 * @var int
	 */
	private $result;
	
	/**
	 * @var ITranslator
	 */
	private $translator;
	
	/**
	 * QuestionGenerator constructor.
	 *
	 * @param string      $htmlId
	 * @param string      $htmlName
	 * @param array       $numbers
	 * @param string      $question
	 * @param ITranslator $translator
	 */
	public function __construct($htmlId, $htmlName, array $numbers, $question, ITranslator $translator = null) {
		$this->htmlName = $htmlName;
		$this->numbers = $numbers;
		$this->question = $question;
		$this->htmlId = $htmlId;
		
		$this->labelPrototype = Html::el(
			sprintf("label for='%s' style='display:block'", $this->getQuestionName())
		);
		
		$this->inputPrototype = Html::el(
			sprintf("input type='text' name='%s' id='%s' required", $this->getQuestionName(), $this->getQuestionId())
		);
		$this->translator = $translator;
	}
	
	/**
	 * @return string
	 */
	public function getContainerId() {
		return sprintf("%s-%s", $this->htmlId, "question");
	}
	
	/**
	 * @return string
	 */
	public function getQuestionName() {
		return sprintf("%s-%s", $this->htmlName, "question-input");
	}
	
	/**
	 * @return string
	 */
	public function getQuestionId() {
		return sprintf("%s-%s", $this->htmlId, "question-input");
	}
	
	/**
	 * @return int
	 */
	public function getResult() {
		return $this->result;
	}
	
	/**
	 * @return Html
	 */
	public function getQuestion() {
		$containerId = $this->getContainerId();
		$container = Html::el("div");
		$container->setAttribute("id", $containerId);
		
		$questionId = $this->getQuestionId();
		
		$first = $this->getRandomNumber();
		$operation = $this->operation[ rand(0, 1) ];
		$second = $this->getRandomNumber();
		$this->result = $result = $this->evalOperation($first, $operation, $second);
		$this->labelPrototype->setText($this->createQuestion($first, $operation, $second));
		
		$container->addHtml($this->labelPrototype);
		$container->addHtml($this->inputPrototype);
		
		$script = Html::el("script");
		$script->setHtml(
			"document.getElementById('$containerId').style.display = 'none';\n" .
			"document.getElementById('$questionId').value = $result;"
		);
		$container->addHtml($script);
		
		return $container;
	}
	
	/**
	 * @return int
	 */
	private function getRandomNumber() {
		return rand(0, 9);
	}
	
	/**
	 * @param int    $first
	 * @param string $operation
	 * @param int    $second
	 * @return int
	 */
	private function evalOperation($first, $operation, $second) {
		switch($operation) {
			case "+":
				return $first + $second;
			case "-":
				return $second > $first ? $second - $first : $first - $second;
		}
		
		return 0;
	}
	
	/**
	 * @param int    $first
	 * @param string $operation
	 * @param int    $second
	 * @return string
	 */
	private function createQuestion($first, $operation, $second) {
		if($operation == "-" && $second > $first) {
			$tmp = $first;
			$first = $second;
			$second = $tmp;
		}
		
		$first = $this->stringify($first);
		$second = $this->stringify($second);
		
		$question = $this->translator === null ? $this->question : $this->translator->translate($this->question);
		
		return sprintf("%s %s %s %s?", $question, $first, $operation, $second);
	}
	
	/**
	 * @param int $number
	 * @return int|string
	 */
	private function stringify($number) {
		if(rand(0, 1)) {
			$number = $this->numbers[$number];
			return $this->translator === null ? $number : $this->translator->translate($number);
		} else {
			return $number;
		}
	}
	
	/**
	 * @return Html
	 */
	public function getLabelPrototype() {
		return $this->labelPrototype;
	}
	
	/**
	 * @param Html $labelPrototype
	 */
	public function setLabelPrototype($labelPrototype) {
		$this->labelPrototype = $labelPrototype;
	}
	
	/**
	 * @return Html
	 */
	public function getInputPrototype() {
		return $this->inputPrototype;
	}
	
	/**
	 * @param Html $inputPrototype
	 */
	public function setInputPrototype($inputPrototype) {
		$this->inputPrototype = $inputPrototype;
	}
}