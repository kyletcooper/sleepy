<?php

namespace WRD\Sleepy\Fields\Concerns;

use Closure;
use WRD\Sleepy\Layouts\Layout;

trait Output {
	use Touch;
	
	protected ?Closure $readResolver = null;

	protected ?Layout $layout = null;

	public function read( ?Closure $readResolver ): static{
		$this->readResolver = $readResolver;

		return $this;
	}

	static public function layout( Layout $layout ): static{
		$inst = new static;
		$inst->layout = $layout;
		
		$inst->importSchema( $layout->schema() );

		return $inst;
	}
	
	public function getOutputValue( string $name, mixed $model, bool $applyLayout = true ): mixed {
		if( ! is_null( $this->alias ) ){
			$name = $this->alias;
		}

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