<?php

namespace WRD\Sleepy\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \WRD\Sleepy\Api\Base base( string $path, ?Closure $group )
 * @method static \WRD\Sleepy\Api\Base getBase()
 * @method static \WRD\Sleepy\Api\Group namespace( string $namespace, ?Closure $group )
 * @method static \WRD\Sleepy\Api\Route route( string $path, ?Closure $group )
 * @method static \WRD\Sleepy\Api\Endpoint endpoint( array|string $method, callable $action )
 *
 * @see \WRD\Sleepy\Api\Router
 */
class API extends Facade{
	protected static function getFacadeAccessor(){
		return 'apiRouter';
	}
}