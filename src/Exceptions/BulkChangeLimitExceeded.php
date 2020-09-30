<?php

namespace Leyden\Schoology\Exceptions;

use Exception;

class BulkChangeLimitExceeded extends Exception {

	/**
	 * The exception code
	 * @var int
	 */
    protected $code = 429;
    
	/**
	 * The exception message
	 * @var string
	 */
    protected $message = "The number of items you are attempting to modify exceeds the limit set by the Schoology API.";

}