<?php

namespace WRD\Sleepy\Schema;

use Illuminate\Support\Str;
use WRD\Sleepy\Schema\Exceptions\CoercedValueException;
use WRD\Sleepy\Schema\Exceptions\CouldNotCoerceException;
use WRD\Sleepy\Schema\Exceptions\NestedSchemaException;
use WRD\Sleepy\Schema\Exceptions\SchemaException;

class Coercer{
	private Schema $schema;

	private mixed $value;

	public function __construct( Schema $schema )
	{
		$this->schema = $schema;
	}

	/**
	 * @throws SchemaException
	 */
	public function coerce( mixed $value ): mixed{
		$this->value = $value;

		try{
			$value = $this->tryCoerce();
		}
		catch( SchemaException $exception ){
			$exception->setSchema( $this->schema );
			$exception->setValue( $this->value );

			$this->value = null;

			throw $exception;
		}

		$this->value = null;
		
		return $value;
	}

	private function tryCoerce(): mixed{
		if( is_null( $this->value ) ){
			return null;
		}

		if( $this->matches() ){
			return $this->value;
		}

		try{
			if( $this->schema->hasType( Schema::OBJECT ) || $this->schema->hasType( Schema::ARRAY ) ) {
				$this->json();
			}

			if( $this->schema->hasType( Schema::NUMBER ) || $this->schema->hasType( Schema::INTEGER ) ) {
				$this->numeric();
			}

			if( $this->schema->hasType( Schema::BOOLEAN ) ) {
				$this->boolean();
			}

			if( $this->schema->hasType( Schema::NULL ) ) {
				$this->null();
			}

			if( $this->schema->hasType( Schema::ARRAY ) && ! $this->schema->hasType( Schema::STRING ) ){
				$this->list();
			}

			if( $this->schema->hasType( Schema::STRING ) ) {
				$this->string();
			}
		}
		catch( CoercedValueException $exception ){
			return $exception->getValue();
		}

		throw new CouldNotCoerceException( "Could not coerce to valid type." );
	}

	private function coerceNestedItems( array $items ): array{
		$subSchema = $this->schema->items;

		if( is_null( $subSchema ) ){
			return $items;
		}

		$coercer = new Coercer( $subSchema );

		foreach( $items as $i => $item ){
			try{
				$items[ $i ] = $coercer->coerce( $item );
			}
			catch( SchemaException $exception ){
				$nested = new NestedSchemaException( "Value at position [$i] could not be coerced." );
				$nested->setPosition( "[$i]" );
				$nested->setChild( $exception );

				throw $nested;
			}
		}

		return $items;
	}

	/**
	 * Not currently used - We do not coerce the contents of JSON strings,
	 */
	private function coerceNestedProperties( array $properties ): array{
		foreach( $properties as $key => $item ){
			$subSchema = null;

			if( array_key_exists( $key, $this->schema->properties ) ){
				$subSchema = $this->schema->properties[ $key ];
			}
			else{
				$subSchema = $this->schema->additionalProperties;
			}

			if( is_null( $subSchema ) ){
				return $properties;
			}

			$coercer = new Coercer( $subSchema );

			try{
				$properties[ $key ] = $coercer->coerce( $item );
			}
			catch( SchemaException $exception ){
				$nested = new NestedSchemaException( "Value at position [$key] could not be coerced." );
				$nested->setPosition( "[$key]" );
				$nested->setChild( $exception );

				throw $nested;
			}
		}

		return $properties;
	}

	private function matches(): bool{
		$lookup = [
			Schema::STRING => "string",
			Schema::NULL => "NULL",
			Schema::NUMBER => "double",
			Schema::INTEGER => "integer",
			Schema::BOOLEAN => "boolean",
			Schema::ARRAY => "array",
			Schema::OBJECT => "array",
		];

		$phpType = gettype( $this->value );

		foreach( $this->schema->types as $allowedType ){
			if( $lookup[ $allowedType ] === $phpType ){
				
				if( $phpType === "array" ){
					// Arrays & Objects have the same PHP type, double check them.
					$isNumeric = array_is_list( $this->value );

					if( $allowedType === Schema::ARRAY && $isNumeric ){
						return true;
					}
					else if( $allowedType === Schema::OBJECT && ! $isNumeric ){
						return true;
					}
				}
				else{
					return true;
				}
			}
		}

		return false;
	}

	private function json(): void{
		if( ! Str::isJson( $this->value ) ){
			return;
		}

		$json = json_decode( $this->value, true );

		if( ! is_array( $json ) ){
			return;
		}

		$isNumeric = array_is_list( $json );

		if( $isNumeric && $this->schema->hasType( Schema::ARRAY ) ){
			// We do not coerce array items for JSON.
			throw new CoercedValueException( $json );
		}
		
		if( ! $isNumeric && $this->schema->hasType( Schema::OBJECT ) ){
			// We do not coerce object properties for JSON.
			throw new CoercedValueException( $json );
		}

		return;
	}

	private function numeric(): void{
		if( ! is_numeric( $this->value ) ){
			return;
		}

		if( $this->schema->hasType( Schema::NUMBER ) ){
			throw new CoercedValueException( floatval( $this->value ) );
		}
		
		if( $this->schema->hasType( Schema::INTEGER ) ){
			throw new CoercedValueException( intval( $this->value ) );
		}
	}

	private function boolean(): void{
		$truthy = [1, '1', true, 'true', 'on'];
		$falsey = [0, '0', false, 'false', 'off'];

		if( in_array( $this->value, $truthy, true ) ){
			throw new CoercedValueException( true );
		}
		
		if( in_array( $this->value, $falsey, true ) ){
			throw new CoercedValueException( false );
		}

		return;
	}

	private function null(): void{
		$nullable = ['', null];

		if( in_array( $this->value, $nullable, true ) ){
			throw new CoercedValueException( null );
		}

		return;
	}

	private function list(): void{
		$list = explode( ",", $this->value );

		foreach( $list as $i => $item ){
			if( strlen( trim( $item ) ) === 0 ){
				// Remove empty values.
				unset( $list[ $i ] );
			}
		}

		if( count( $list ) === 0 ){
			return;
		}

		$list = $this->coerceNestedItems( $list );

		throw new CoercedValueException( $list );
	}

	private function string(): void{
		if( false === $this->value || null === $this->value ){
			return;
		}

		$string = trim( strval( $this->value ) );

		if( strlen( $string ) === 0 ){
			return;
		}

		throw new CoercedValueException( $string );
	}
}