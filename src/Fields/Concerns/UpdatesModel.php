<?php

namespace WRD\Sleepy\Fields\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait UpdatesModel {
	public ?Closure $updateCallback;

	public function update( Closure $updateCallback ): static {
		$this->updateCallback = $updateCallback;

		return $this;
	}

	protected function defaultUpdate( Model $model, string $name, mixed $value ): Model{
		return $model;
	}

	public function updateModel( Model $model, string $name, mixed $value ): Model{
		if( ! isset( $this->updateCallback ) ){
			return $this->defaultUpdate( $model, $name, $value );
		}

		return call_user_func( $this->updateCallback, $model, $name, $value, $this );
	}
}