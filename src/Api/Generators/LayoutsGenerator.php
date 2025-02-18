<?php

namespace WRD\Sleepy\Api\Generators;

use WRD\Sleepy\Api\Route;
use WRD\Sleepy\Support\Facades\API;

class LayoutsGenerator extends Generator{
	public string $path;

	public function __construct( string $path )
	{
		$this->path = $path;		
	}

	public function create(): Route{
		// Hello world.

		return API::route( $this->path );
	}
}