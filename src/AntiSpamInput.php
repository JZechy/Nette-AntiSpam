<?php

namespace Zet\AntiSpam;

/**
 * Class AntiSpamControl
 * @author Zechy <email@zechy.cz>
 * @package Zet\AntiSpam\AntiSpamInput
 */
class AntiSpamControl extends \Nette\Forms\Controls\BaseControl {

	# --------------------------------------------------------------------
	# Initial configuration
	# --------------------------------------------------------------------
	/**
	 * Registrace AntiSpamu.
	 * @static
	 * @param $systemContainer
	 */
	public static function register($systemContainer) {
		$class = __CLASS__;
		\Nette\Forms\Container::extensionMethod("addAntiSpam", function (
			\Nette\Forms\Container $container, $name, $blockingTime = 0, $minimumReadTime = 60
		) use ($class, $systemContainer) {
			$component = new $class($name, $blockingTime, $minimumReadTime);
			$component->setContainer($systemContainer);
			$container->addComponent($component, $name);

			return $component;
		});
	}

	# --------------------------------------------------------------------
	# Control definition
	# --------------------------------------------------------------------
	/**
	 * @var \Nette\DI\Container
	 */
	private $container;

	/**
	 * @var \Nette\Http\Request
	 */
	private $request;

	/**
	 * @var SpamSession
	 */
	private $session;

	/**
	 * Název skupiny antispamových elementů.
	 * @var string
	 */
	private $name;

	/**
	 * Název třídy, která se má používat pro skrývání přídavných polí.
	 * Pokud není nastaveno, je skrytí elementů přidáno do style.
	 * @var string
	 */
	private $hiddenClass;

	/**
	 * Kolik vteřin musí uběhnout od doby, než uživatel může znova odeslat formulář.
	 * @var int
	 */
	private $blockingTime = 0;

	/**
	 * Kolik vteřin musí uběhnout od načtení stránky, než uživatle může poprvé odeslat formulář.
	 * @var int
	 */
	private $minimumReadTime = 60;

	/**
	 * Matematické operace pro vygenerování příkladu.
	 * @var array
	 */
	private $operations = array("+", "-", "*");

	/**
	 * Slovní vyjádření čísel.
	 * @var array
	 */
	private $numberStrings = array(
		"Nula", "Jedna", "Dva", "Tři", "Čtyři", "Pět", "Šest", "Sedm", "Osm", "Devět"
	);

	/**
	 * Otázka vyzívacající užitele k výpočtu.
	 * @var string
	 */
	private $question = "Vypočítejte ";

	/**
	 * Výsledek početního příkladu.
	 * @var int
	 */
	private $result = 0;

	/**
	 * @var int
	 */
	private $errorType = 0;

	/**
	 * AntiSpamInput constructor.
	 * @param string $name Název elementu.
	 * @param int $blockingTime Kolik vteřin musí uběhnout od doby, než uživatel může znova odeslat formulář.
	 * @param int $minimumReadTime Kolik vteřin musí uběhnout od načtení stránky, než uživatle může poprvé odeslat
	 *     formulář.
	 */
	public function __construct($name, $blockingTime = 0, $minimumReadTime = 60) {
		parent::__construct(NULL);
		$this->name = $name;
		$this->blockingTime = $blockingTime;
		$this->minimumReadTime = $minimumReadTime;
		$this->session = new SpamSession($name);
	}

	# --------------------------------------------------------------------
	# Setters
	# --------------------------------------------------------------------
	/**
	 * Nastaví název třídy, která se použije při vygenerování skrytých inputů.
	 * @param string $className
	 * @return $this
	 */
	public function setHiddenClass($className) {
		$this->hiddenClass = $className;

		return $this;
	}

	/**
	 * Nastaví kolik vteřin musí uběhnout od doby, než uživatel může znova odeslat formulář.
	 * @param int $blockingTime
	 * @return $this
	 */
	public function setBlockingTime($blockingTime) {
		$this->blockingTime = $blockingTime;

		return $this;
	}

	/**
	 * Nastaví kolik vteřin musí uběhnout od načtení stránky, než uživatle může poprvé odeslat formulář.
	 * @param int $minimumReadTime
	 * @return $this
	 */
	public function setMinimumReadTime($minimumReadTime) {
		$this->minimumReadTime = $minimumReadTime;

		return $this;
	}

	/**
	 * Nastaví slovní vyjádření čísel pro JavaScript otázku.
	 * @param array $numberStrings
	 * @return $this
	 */
	public function setNumberStrings($numberStrings) {
		$this->numberStrings = $numberStrings;

		return $this;
	}

	/**
	 * Nastaví otázku pro vyzvání uživatele pro výpočet.
	 * @param string $question
	 * @return $this
	 */
	public function setQuestion($question) {
		$this->question = $question;

		return $this;
	}

	/**
	 * Nastaví session omezení.
	 */
	private function setLimitations() {
		$this->session->write("minimumReadTime", strtotime("+ " . $this->minimumReadTime . " seconds"));
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @internal
	 */
	public function setContainer($container) {
		$this->container = $container;
	}

	/**
	 * @return \Nette\Http\Request
	 */
	private function getRequest() {
		if(is_null($this->request)) {
			$this->request = $this->container->getByType("\Nette\Http\Request");
		}

		return $this->request;
	}

	# --------------------------------------------------------------------
	# Input generator
	# --------------------------------------------------------------------
	/**
	 * Vygenerování inputu.
	 * @return \Nette\Utils\Html
	 */
	public function getControl() {
		$this->setOption('rendered', TRUE);
		$this->setLimitations();

		$divGroup = \Nette\Utils\Html::el("div class='zet-" . $this->getHtmlName() . "-group'");
		$this->generateHiddenInputs($divGroup);
		$this->generateJavaScriptQuestion($divGroup);

		$this->session->dumpSession();

		return $divGroup;
	}

	/**
	 * Vygeneruje doplňující pole skrytá uživateli přes CSS.
	 * @param \Nette\Utils\Html $group
	 */
	private function generateHiddenInputs(\Nette\Utils\Html &$group) {
		$hiddenGroup = \Nette\Utils\Html::el("div");
		if(is_null($this->hiddenClass)) {
			$hiddenGroup->addAttributes(array(
				"style" => "display:none"
			));
		} else {
			$hiddenGroup->class[] = $this->hiddenClass;
		}

		$textInput = \Nette\Utils\Html::el("input type='text' name='" . $this->getHtmlName() . "-name'");
		$hiddenGroup->add($textInput);

		$checkBox = \Nette\Utils\Html::el("input type='checkbox' name='" . $this->getHtmlName() . "-terms'");
		$hiddenGroup->add($checkBox);

		$group->add($hiddenGroup);
	}

	/**
	 * Vygeneruje početní otázku pro uživatele, která se bude doplňovat JavaScriptem.
	 * @param \Nette\Utils\Html $group
	 */
	private function generateJavaScriptQuestion(\Nette\Utils\Html &$group) {
		$groupId = $this->getHtmlId() . "-question-group";
		$javaScriptGroup = \Nette\Utils\Html::el("div id='$groupId'");
		$javaScriptGroup->class[] = $this->getHtmlName() . "-question-group";

		$label = \Nette\Utils\Html::el("label");
		$label->setText($this->generateMathQuestion());

		$inputName = $this->getHtmlName() . "-question-result";
		$inputId = $this->getHtmlId() . "-question-result";
		$input = \Nette\Utils\Html::el("input type='text' name='$inputName' id='$inputId'");
		$javaScriptGroup->add($label);
		$javaScriptGroup->add($input);

		$script = \Nette\Utils\Html::el("script");
		$script->setHtml("
			document.getElementById('$inputId').value = " . $this->result . ";
			document.getElementById('$groupId').style.display = 'none';
		");
		$javaScriptGroup->add($script);

		$group->add($javaScriptGroup);
	}

	/**
	 * Vygeneruje příklad s výsledkem a vrátí otázku na něj.
	 * @return string
	 */
	private function generateMathQuestion() {
		$numberA = rand(0, 9);
		$numberB = rand(0, 9);

		$operation = $this->selectRandomOperation();
		switch($operation) {
			case "+":
				$this->result = $numberA + $numberB;
				break;
			case "-":
				if($numberA < $numberB) {
					$oldA = $numberA;
					$oldB = $numberB;
					$numberA = $oldB;
					$numberB = $oldA;
				}
				$this->result = $numberA - $numberB;
				break;
			case "*":
				$this->result = $numberA * $numberB;
				break;
		}
		$numberA = $this->stringifyNumber($numberA);
		$numberB = $this->stringifyNumber($numberB);
		$this->session->write("result", $this->result);

		return "Vypočítejte $numberA $operation $numberB";
	}

	/**
	 * Vrátí náhodnou matematickou operaci.
	 * @return string
	 */
	private function selectRandomOperation() {
		return $this->operations[ rand(0, count($this->operations) - 1) ];
	}

	/**
	 * Náhodně se určí, zda se z čísla udělá řetězec nebo se vrátí v původním stavu.
	 * @param int $number
	 * @return mixed
	 */
	private function stringifyNumber($number) {
		if(rand(0, 1) == 1) {
			$number = $this->numberStrings[ $number ];
		}

		return $number;
	}

	# --------------------------------------------------------------------
	# GetValue
	# --------------------------------------------------------------------
	/**
	 * Vrátí vyhodnocení, zda formulář neodesílá bot.
	 * @return int
	 */
	public function getValue() {
		$this->checkReadTime();
		$this->checkBlockingTime();
		$this->checkHiddenFields();
		$this->checkMathResult();

		return $this->errorType;
	}

	/**
	 * @return int
	 */
	public function getErrorType() {
		return $this->errorType;
	}

	/**
	 * @return bool
	 */
	private function checkReadTime() {
		if($this->session->read("minimumReadTime") > time()) {
			$this->errorType = ErrorType::MINIMUM_READ_TIME;
		}
	}

	/**
	 * Ověří, zda byl správně vypočítán příklad.
	 * @return bool
	 */
	private function checkMathResult() {
		$result = $this->session->read("result");
		$enteredValue = $this->getRequest()->getPost($this->getHtmlName() . "-question-result");

		if($result != $enteredValue) {
			$this->errorType = ErrorType::WRONG_RESULT;
		}
	}

	/**
	 * Ověří, zda nejsou vyplněná skrytá pole.
	 */
	private function checkHiddenFields() {
		$text = $this->getRequest()->getPost($this->getHtmlName() . "-name");
		$checkbox = $this->getRequest()->getPost($this->getHtmlName() . "-terms");

		if(!empty($text) || is_array($checkbox)) {
			$this->errorType = ErrorType::FILLED_HIDDEN_FIELDS;
		}
	}

	/**
	 * @return bool
	 */
	private function checkBlockingTime() {
		$blockingTime = $this->session->read("blockingTime");
		if(is_null($blockingTime)) {
			if($this->errorType == 0) {
				$this->session->write("blockingTime", strtotime("+ " . $this->blockingTime . " seconds"));
			}
		} else {
			if($blockingTime > time()) {
				$this->errorType = ErrorType::BLOCKING_TIME;
			}
		}
	}

	# --------------------------------------------------------------------
	# Not Supported
	# --------------------------------------------------------------------
	/**
	 * @param $value
	 * @return \Nette\NotSupportedException
	 */
	public function setDefaultValue($value) {
		throw new \Nette\NotSupportedException("Funkce setDefaultValue() není podporována.");
	}

	/**
	 * @param $validator
	 * @param null $message
	 * @param null $arg
	 * @return \Nette\Forms\Controls\BaseControl|void
	 */
	public function addRule($validator, $message = NULL, $arg = NULL) {
		throw new \Nette\NotSupportedException("Funkce addRule() není podporována.");
	}

	/**
	 * @param $validator
	 * @param null $value
	 * @return \Nette\Forms\Rules|void
	 */
	public function addCondition($validator, $value = NULL) {
		throw new \Nette\NotSupportedException("Funkce addCondition() není podporována.");
	}

	/**
	 * @param \Nette\Forms\IControl $control
	 * @param $validator
	 * @param null $value
	 * @return \Nette\Forms\Rules|void
	 */
	public function addConditionOn(\Nette\Forms\IControl $control, $validator, $value = NULL) {
		throw new \Nette\NotSupportedException("Funkce addConditionOn() není podporována.");
	}

	/**
	 * @param bool $value
	 * @return \Nette\Forms\Controls\BaseControl|void
	 */
	public function setRequired($value = TRUE) {
		throw new \Nette\NotSupportedException("Funkce setRequired() není podporována.");
	}
}

/**
 * Class ErrorType
 * @author Zechy <email@zechy.cz>
 * @package Zet\AntiSpam
 */
class ErrorType extends \Nette\Object {

	/**
	 * Špatný výsledek otázky.
	 * @var int
	 */
	const WRONG_RESULT = 1;

	/**
	 * Jsou vyplněná skrytá pole.
	 * @var int
	 */
	const FILLED_HIDDEN_FIELDS = 2;

	/**
	 * Neuplynula doba, po kterou nemůže uživatel posílat další příspěvěk.
	 * @var int
	 */
	const BLOCKING_TIME = 3;

	/**
	 * Neuplynula doba od načtení, po které může uživatel odeslat formulář.
	 * @var int
	 */
	const MINIMUM_READ_TIME = 4;
}