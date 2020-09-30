<?php

namespace Leyden\Schoology\Exceptions;

use Exception;

class MethodNotImplemented extends Exception {

	/**
	 * The exception code
	 * @var int
	 */
    protected $code = 404;
    
	/**
	 * The exception message
	 * @var string
	 */
    protected $message = "This call is not availble for this resource.";

}