<?php

namespace WRD\Sleepy\Http\Exceptions;

class ApiNotFoundException extends ApiException{
	public function __construct( string $message = null )
	{
		if( is_null( $message ) ){
			$message = "Not found";
		}

		parent::__construct( 404, $message );
	}
}