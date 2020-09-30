<?php

namespace Leyden\Schoology\Exceptions;

use Exception;

class PathRequiresParentParameters extends Exception {

	/**
	 * The exception code
	 * @var int
	 */
    protected $code = 400;
    
	/**
	 * The exception message
	 * @var string
	 */
    protected $message = "The method you tried to call requires a parent to be specified, but none was found.";

}