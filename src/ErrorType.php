<?php

namespace Zet\AntiSpam;

use Nette\StaticClass;

/**
 * Class ErrorType
 *
 * @author  Zechy <email@zechy.cz>
 * @package Zet\AntiSpam
 */
class ErrorType {
	
	use StaticClass;
	
	const NO_ERROR = 0;
	
	const LOCK_TIME = 1;
	
	const RESEND_TIME = 2;
	
	const HIDDEN_FIELDS = 3;
	
	const QUESTION = 4;
}