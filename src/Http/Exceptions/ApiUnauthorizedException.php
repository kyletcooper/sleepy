<?php

namespace WRD\Sleepy\Http\Exceptions;

class ApiUnauthorizedException extends ApiException{
	public function __construct( string $message = null )
	{
		if( is_null( $message ) ){
			$message = "Unauthorized";
		}

		parent::__construct( 401, $message );
	}
}