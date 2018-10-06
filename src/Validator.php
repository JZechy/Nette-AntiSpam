<?php

namespace Zet\AntiSpam;

use Nette\Http\Request;
use Nette\Http\Session;
use Tracy\Debugger;

/**
 * Class Validator
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\AntiSpam
 */
class Validator {
	
	/**
	 * @var Session
	 */
	private $session;
	
	/**
	 * @var Request
	 */
	private $request;
	
	/**
	 * @var string
	 */
	private $method;
	
	/**
	 * @var string
	 */
	private $htmlName;
	
	/**
	 * @var array
	 */
	private $hiddenInputs;
	
	/**
	 * @var string
	 */
	private $questionInput;
	
	/**
	 * @var string
	 */
	private $htmlId;
	
	/**
	 * @var int
	 */
	private $error = ErrorType::NO_ERROR;
	
	/**
	 * Validator constructor.
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request) {
		$this->request = $request;
	}
	
	/**
	 * @param string $method
	 */
	public function setFormMethod($method) {
		$this->method = $method;
	}
	
	/**
	 * @param string     $key
	 * @param null|mixed $defaultValue
	 * @return mixed
	 */
	public function getData($key, $defaultValue = null) {
		switch($this->method) {
			case "get":
				return $this->request->getQuery($key, $defaultValue);
			default:
				return $this->request->getPost($key, $defaultValue);
		}
	}
	
	/**
	 * @return \Nette\Http\SessionSection|\stdClass
	 */
	public function getSessionSection() {
		return $this->getSession()->getSection(sprintf("antispam-%s", $this->htmlId));
	}
	
	/**
	 * @param string $htmlName
	 */
	public function setHtmlName($htmlName) {
		$this->htmlName = $htmlName;
	}
	
	/**
	 * @param array $inputs
	 */
	public function setHiddenInputs(array $inputs) {
		$this->hiddenInputs = $inputs;
	}
	
	/**
	 * @param string $questionInput
	 */
	public function setQuestionInput($questionInput) {
		$this->questionInput = $questionInput;
	}
	
	/**
	 * @param int $questionResult
	 */
	public function setQuestionResult($questionResult) {
		$this->getSessionSection()->result = $questionResult;
	}
	
	/**
	 * @param int $lockTime
	 */
	public function setLockTime($lockTime) {
		$this->getSessionSection()->locked = strtotime("+ $lockTime seconds");
	}
	
	/**
	 * @param int $resendTime
	 */
	public function setResendTime($resendTime) {
		if($resendTime > 0) {
			$this->getSessionSection()->resend = strtotime("+ $resendTime seconds");
		}
	}
	
	/**
	 * @return int
	 */
	public function getError() {
		return $this->error;
	}
	
	/**
	 * @return bool
	 */
	public function validateForm() {
		if(!$this->validateHiddenFields()) {
			$this->error = ErrorType::HIDDEN_FIELDS;
			
			return false;
		}
		if(!$this->validateQuestion()) {
			$this->error = ErrorType::QUESTION;
			
			return false;
		}
		if(!$this->validateLock()) {
			$this->error = ErrorType::LOCK_TIME;
			
			return false;
		}
		if(!$this->validateResendTime()) {
			$this->error = ErrorType::RESEND_TIME;
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	private function validateHiddenFields() {
		foreach($this->hiddenInputs as $name => $type) {
			$postKey = sprintf("%s-%s", $this->htmlName, $name);
			if($type == "checkbox") {
				$value = $this->getData($postKey, "off");
				
				if($value == "on") {
					return false;
				}
			} else {
				$value = $this->getData($postKey);
				if(!empty($value)) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * @return bool
	 */
	private function validateQuestion() {
		$value = $this->getData($this->questionInput);
		
		return $value == $this->getSessionSection()->result;
	}
	
	/**
	 * @return bool
	 */
	private function validateLock() {
		return $this->getSessionSection()->locked <= time();
	}
	
	/**
	 * @return bool
	 */
	private function validateResendTime() {
		if(isset($this->getSessionSection()->resend)) {
			return $this->getSessionSection()->resend <= time();
		} else {
			return true;
		}
	}
	
	public function barDumpSession() {
		Debugger::barDump("Výsledek: ". $this->getSessionSection()->result);
		Debugger::barDump("Blokován do: ". date("H:i:s", $this->getSessionSection()->locked));
		if($this->getSessionSection()->resend == null) {
			$time = "---";
		} else {
			$time = date("H:i:s", $this->getSessionSection()->resend);
		}
		Debugger::barDump("Znovuodeslání v: ". $time);
	}
	
	/**
	 * @param Session $session
	 */
	public function setSession(Session $session) {
		$this->session = $session;
	}
	
	/**
	 * @return Session
	 */
	public function getSession() {
		if (!$this->session) {
			$this->session = new \Nette\Http\Session($this->request, new \Nette\Http\Response);
		}
		return $this->session;
	}
	
	/**
	 * @param string $htmlId
	 */
	public function setHtmlId($htmlId) {
		$this->htmlId = $htmlId;
	}
}
