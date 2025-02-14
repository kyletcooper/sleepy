<?php

namespace WRD\Sleepy\Schema\Exceptions;

use Exception;

class CoercedValueException extends Exception{
	public mixed $value;

	public function __construct( mixed $value )
	{
		$this->value = $value;
	}

	public function getValue(){
		return $this->value;
	}
}