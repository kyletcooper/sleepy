<?php

namespace WRD\Sleepy\Http\Exceptions;

class ApiUnauthenticatedException extends ApiException{
	public function __construct( string $message = null )
	{
		if( is_null( $message ) ){
			$message = "Unauthenticated";
		}

		parent::__construct( 401, $message );
	}
}