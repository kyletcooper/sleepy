<?php

namespace WRD\Sleepy\Support;

use Closure;

trait HasHooks{
	static private array $hookCallbacks = [];

	static public function addHook( string|array $hookName, Closure $callback ): void{
		if( is_array( $hookName ) ){
			foreach( $hookName as $name ){
				static::addHook( $name, $callback );
			}
			
			return;
		}

		if( ! array_key_exists( $hookName, static::$hookCallbacks ) ){
			static::$hookCallbacks[ $hookName ] = [];
		}

		static::$hookCallbacks[ $hookName ][] = $callback;
	}

	static public function runHook( string $hookName, mixed $value, mixed ...$args ): mixed {
		if( ! array_key_exists( $hookName, static::$hookCallbacks ) ){
			return $value;
		}

		$callbacks = static::$hookCallbacks[ $hookName ];

		foreach( $callbacks as $callback ){
			$value = call_user_func( $callback, $value, ...$args );
		}

		return $value;
	}
}