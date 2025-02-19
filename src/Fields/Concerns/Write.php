<?php

namespace WRD\Sleepy\Fields\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait Write {
	public ?Closure $updateCallback;

	public function write( Closure $updateCallback ): static {
		$this->updateCallback = $updateCallback;

		return $this;
	}

	public function writeValue( Model $model, string $name, mixed $value ): Model{
		if( isset( $this->updateCallback ) ){
			return call_user_func( $this->updateCallback, $model, $name, $value, $this );
		}
		else{
			$model->{$name}	= $value;

			return $model;
		}
	}
}