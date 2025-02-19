<?php

namespace WRD\Sleepy\Fields\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait Write {
	use Touch;
	
	public ?Closure $updateCallback;

	public function write( Closure $updateCallback ): static {
		$this->updateCallback = $updateCallback;

		return $this;
	}

	public function writeValue( Model $model, string $name, mixed $value ): Model{
		if( ! is_null( $this->alias ) ){
			$name = $this->alias;
		}

		if( isset( $this->updateCallback ) ){
			return call_user_func( $this->updateCallback, $model, $name, $value, $this );
		}
		else{
			$model->{$name}	= $value;

			return $model;
		}
	}
}