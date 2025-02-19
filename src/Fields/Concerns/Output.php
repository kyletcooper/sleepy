<?php

namespace WRD\Sleepy\Fields\Concerns;

use Closure;

trait Output {
	protected ?Closure $readResolver = null;

	public function read( ?Closure $readResolver ): static{
		$this->readResolver = $readResolver;

		return $this;
	}
	
	public function getOutputValue( string $name, mixed $model, bool $applyLayout = true ): mixed {
		$value = null;

		if( ! is_null( $this->readResolver ) ){
			$value = call_user_func( $this->readResolver, $name, $model, $this );
		}
		else {
			$value = $model->{$name};
		}

		if( ! is_null( $value ) && ! is_null( $this->layout ) && $applyLayout ){
			$value = $this->layout->present( $value );
		}

		return $value;
	}
}