<?php

namespace Zet\AntiSpam;

use Nette;
use Nette\DI\CompilerExtension;

/**
 * Class AntiSpamExtension
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\AntiSpam
 */
final class AntiSpamExtension extends CompilerExtension {
	
	/**
	 * @var array
	 */
	private $defaults = [
		"lockTime" => 5,
		"resendTime" => 60,
		"numbers" => [
			"nula", "jedna", "dva", "tři", "čtyři", "pět", "šest", "sedm", "osm", "devět"
		],
		"question" => "Kolik je",
		"translate" => false
	];
	
	/**
	 * @var array
	 */
	private $configuration = [];
	
	/**
	 *
	 */
	public function loadConfiguration() {
		$this->configuration = $this->getConfig($this->defaults);
	}
	
	/**
	 * @param Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$init = $class->methods["initialize"];
		
		$init->addBody('\Zet\AntiSpam\AntiSpamControl::register(?, $this->getService(?), $this->getService(?));', [
			$this->configuration,
			$this->getContainerBuilder()->getByType(Nette\Http\Session::class),
			$this->getContainerBuilder()->getByType(Nette\Http\Request::class)
		]);
	}
}