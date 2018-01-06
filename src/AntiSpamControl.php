<?php

namespace Zet\AntiSpam;

use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;

/**
 * Class AntiSpamControl
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\AntiSpam
 */
class AntiSpamControl extends BaseControl {
	
	# --------------------------------------------------------------------
	# Registration
	# --------------------------------------------------------------------
	/**
	 * @param array $configuration
	 */
	public static function register(array $configuration) {
		$class = __CLASS__;
		
		Container::extensionMethod("addAntiSpam", function(
			Container $container, $name, $lockTime = 5, $resendTime = 60
		) use ($class, $configuration) {
			$control = new $class($configuration, $name, $lockTime, $resendTime);
			$container->addComponent($control, $name);
			
			return $control;
		});
	}
	
	# --------------------------------------------------------------------
	# Control definition
	# --------------------------------------------------------------------
	/**
	 * @var array
	 */
	private $configuration = [
		"lockTime" => null,
		"resendTime" => null,
		"numbers" => [],
		"question" => null
	];
	
	/**
	 * @var HiddenFields
	 */
	private $hiddenFields;
	
	/**
	 * @var Question
	 */
	private $question;
	
	/**
	 * AntiSpamControl constructor.
	 *
	 * @param array  $configuration
	 * @param string $name
	 * @param int    $lockTime
	 * @param int    $resendTime
	 */
	public function __construct(array $configuration, $name, $lockTime, $resendTime) {
		parent::__construct($name);
		
		$this->configuration = $configuration;
		$this->configuration["lockTime"] = $lockTime;
		$this->configuration["resendTime"] = $resendTime;
	}
	
	/**
	 * @param $form
	 */
	protected function attached($form) {
		parent::attached($form);
		
		$this->hiddenFields = new HiddenFields($this->getHtmlId(), $this->getHtmlName());
		$this->question = new Question(
			$this->getHtmlId(), $this->getHtmlName(), $this->configuration["numbers"], $this->configuration["question"]
		);
	}
	
	/**
	 * @param int $lockTime
	 * @return AntiSpamControl
	 */
	public function setLockTime($lockTime) {
		$this->configuration["lockTime"] = $lockTime;
		
		return $this;
	}
	
	/**
	 * @param int $resendTime
	 * @return AntiSpamControl
	 */
	public function setResendTime($resendTime) {
		$this->configuration["resendTime"] = $resendTime;
		
		return $this;
	}
	
	/**
	 * @param array $numbers
	 * @return AntiSpamControl
	 */
	public function setNumbers(array $numbers) {
		$this->configuration["numbers"] = $numbers;
		
		return $this;
	}
	
	/**
	 * @param string $question
	 * @return AntiSpamControl
	 */
	public function setQuestion($question) {
		$this->configuration["question"] = $question;
		
		return $this;
	}
	
	/**
	 * @return Html
	 */
	public function getControl() {
		$element = parent::getControl();
		$element->setName("div");
		$element->addHtml($this->hiddenFields->getControls());
		$element->addHtml($this->question->getQuestion());
		
		return $element;
	}
	
	/**
	 * @param null $caption
	 * @return \Nette\Utils\Html|string
	 */
	public function getLabel($caption = null) {
		return "";
	}
}