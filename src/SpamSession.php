<?php

namespace Zet\AntiSpam;

/**
 * Class SpamSession
 * @author Zechy <email@zechy.cz>
 * @package Zet\AntiSpam
 */
class SpamSession {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * SpamSession constructor.
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * @param string $index
	 * @param int $value
	 */
	public function write($index, $value) {
		$_SESSION[ $this->name ][ $index ] = $value;
	}

	/**
	 * @param string $index
	 * @return mixed
	 */
	public function read($index) {
		return isset($_SESSION[ $this->name ][ $index ]) ? $_SESSION[ $this->name ][ $index ] : NULL;
	}

	/**
	 * Testovací výpis session.
	 */
	public function dumpSession() {
		\Tracy\Debugger::barDump("Min. Read Time " . date("d. m. Y H:i:s", $_SESSION[ $this->name ]["minimumReadTime"]));
		if(isset($_SESSION[ $this->name ]["blockingTime"])) {
			\Tracy\Debugger::barDump("Blocking time " . date("d. m. Y H:i:s", $_SESSION[ $this->name ]["blockingTime"]));
		}
		\Tracy\Debugger::barDump("Result " . $_SESSION[ $this->name ]["result"]);
	}
}