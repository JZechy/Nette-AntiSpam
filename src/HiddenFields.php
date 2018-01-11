<?php

namespace Zet\AntiSpam;

use Nette\Utils\Html;

/**
 * Class HiddenFields
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\AntiSpam
 */
class HiddenFields {
	
	/**
	 * @var array [inputType => inputName]
	 */
	private $inputs = [
		"url" => "text",
		"email" => "text",
		"rules" => "checkbox"
	];
	
	/**
	 * @var string
	 */
	private $htmlId;
	
	/**
	 * @var string
	 */
	private $htmlName;
	
	/**
	 * @var string
	 */
	private $hideClass;
	
	/**
	 * @return string
	 */
	public function getGroupId() {
		return sprintf("%s-%s", $this->htmlId, "fields");
	}
	
	/**
	 * @return Html
	 */
	public function getControls() {
		$groupId = $this->getGroupId();
		$group = Html::el("div");
		$group->setAttribute("id", $groupId);
		
		foreach($this->inputs as $name => $type) {
			$el = Html::el("input");
			$el->setAttribute("type", $type);
			$el->setAttribute("name", sprintf("%s-%s", $this->htmlName, $name));
			$group->addHtml($el);
		}
		
		if($this->hideClass === null) {
			$script = Html::el("script");
			$script->setHtml("document.getElementById('$groupId').style.display = 'none';");
			$group->addHtml($script);
		} else {
			$group->appendAttribute("class", $this->hideClass);
		}
		
		
		return $group;
	}
	
	/**
	 * @param string $class
	 */
	public function hideByClass($class) {
		$this->hideClass = $class;
	}
	
	/**
	 * @return array
	 */
	public function getInputs() {
		return $this->inputs;
	}
	
	/**
	 * @param string $htmlId
	 */
	public function setHtmlId($htmlId) {
		$this->htmlId = $htmlId;
	}
	
	/**
	 * @param string $htmlName
	 */
	public function setHtmlName($htmlName) {
		$this->htmlName = $htmlName;
	}
}