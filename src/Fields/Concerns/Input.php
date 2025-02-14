<?php

namespace WRD\Sleepy\Fields\Concerns;

trait Input {
	public function getInputValue( string $name, array $values ): mixed {
		$value = $this->coerce( $values[ $name ] ?? null );

		$this->validate( $value );

		if( is_null( $value ) ){
			// We assign a default value AFTER validation.
			// Otherwise 'required' would do nothing (there would always be a value).
			$value = $this->default;
		}

		return $value;
	}
}