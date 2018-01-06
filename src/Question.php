<?php

namespace Zet\AntiSpam;

use Nette\Utils\Html;

/**
 * Class Question
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\AntiSpam
 */
class Question {
	
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
	 * Question constructor.
	 *
	 * @param string $htmlId
	 * @param string $htmlName
	 * @param array  $numbers
	 * @param string $question
	 */
	public function __construct($htmlId, $htmlName, array $numbers, $question) {
		$this->htmlName = $htmlName;
		$this->numbers = $numbers;
		$this->question = $question;
		$this->htmlId = $htmlId;
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
	 * @return Html
	 */
	public function getQuestion() {
		$containerId = $this->getContainerId();
		$container = Html::el("div");
		$container->setAttribute("id", $containerId);
		
		$questionName = $this->getQuestionName();
		$questionId = $this->getQuestionId();
		$first = $this->getRandomNumber();
		$operation = $this->operation[ rand(0, 1) ];
		$second = $this->getRandomNumber();
		$result = $this->evalOperation($first, $operation, $second);
		
		$label = Html::el("label for='$questionName' style='display:block'");
		$label->setText($this->createQuestion($first, $operation, $second));
		$container->addHtml($label);
		$input = Html::el("input type='text' name='$questionName' id='$questionId'");
		$container->addHtml($input);
		
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
		
		return sprintf("%s %s %s %s?", $this->question, $first, $operation, $second);
	}
	
	/**
	 * @param int $number
	 * @return int|string
	 */
	private function stringify($number) {
		return rand(0, 1) ? $this->numbers[ $number ] : $number;
	}
}