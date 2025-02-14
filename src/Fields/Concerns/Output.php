<?php

namespace WRD\Sleepy\Fields\Concerns;

use Closure;
use WRD\Sleepy\Schema\Layouts\Layout;

trait Output {
	public ?Closure $outputResolver = null;

	public ?Closure $presenter = null;

	public function getOutputVia( ?Closure $outputResolver ): static{
		$this->outputResolver = $outputResolver;

		return $this;
	}

	public function presenter( ?Closure $presenter ): static{
		$this->presenter = $presenter;

		return $this;
	}

	public function layout( Layout $layout ): static{
		$this->presenter( $layout->getPresenter() );

		$this->importSchema( $layout->getSchema() );

		return $this;
	}
	
	public function getOutputValue( string $name, mixed $model, bool $present = true ): mixed {
		$value = null;

		if( ! is_null( $this->outputResolver ) ){
			$value = call_user_func( $this->outputResolver, $name, $model, $this );
		}
		else {
			$value = $model->{$name};
		}

		if( is_null( $value ) ){
			return null;
		}

		if( ! is_null( $this->presenter ) && $present ){
			$value = call_user_func( $this->presenter, $value, $name, $model, $this );
		}

		return $value;
	}
}