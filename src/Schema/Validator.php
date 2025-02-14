<?php

namespace WRD\Sleepy\Schema;

use Illuminate\Support\Facades\Validator as FacadesValidator;
use WRD\Sleepy\Schema\Exceptions\CompositionException;
use WRD\Sleepy\Schema\Exceptions\CustomRuleException;
use WRD\Sleepy\Schema\Exceptions\DuplicateItemException;
use WRD\Sleepy\Schema\Exceptions\InvalidPropertyKeyException;
use WRD\Sleepy\Schema\Exceptions\InvalidTypeException;
use WRD\Sleepy\Schema\Exceptions\NestedSchemaException;
use WRD\Sleepy\Schema\Exceptions\OutOfEnumException;
use WRD\Sleepy\Schema\Exceptions\OutOfRangeException;
use WRD\Sleepy\Schema\Exceptions\PatternMismatchException;
use WRD\Sleepy\Schema\Exceptions\RequiredException;
use WRD\Sleepy\Schema\Exceptions\SchemaException;

class Validator {
	private Schema $schema;

	private mixed $value = null;

	public function __construct( Schema $schema )
	{
		$this->schema = $schema;
	}

	public function validate( mixed $value ): void{
		$this->value = $value;

		try{
			$this->tryValidations();
		}
		catch( SchemaException $exception ){
			$exception->setSchema( $this->schema );
			$exception->setValue( $this->value );

			$this->value = null;

			throw $exception;
		}

		$this->value = null;
	}

	public function isValid( mixed $value ): bool{
		try{
			$this->validate( $value );

			return true;
		}
		catch( SchemaException $exception ){
			// Nothing.
		}

		return false;
	}

	protected function tryValidations(){
		$this->required();

		if( ! $this->schema->required && is_null( $this->value ) ){
			return;
		}

		if( $this->schema->hasComposition() ){
			$this->composition();

			return;
		}

		$this->type();
		$this->const();
		$this->enum();
		$this->format();

		switch( gettype( $this->value ) ){
			case "string":
				$this->string();
				break;

			case "double":
			case "float":
			case "integer":
				$this->numeric();
				break;

			case "array":
				$this->array();
				$this->object();
				break;
		}

		$this->custom();

		return;
	}

	private function required(){
		if( $this->schema->required === true && is_null( $this->value ) ){
			throw new RequiredException( "The value is required." );
		}
	}

	private function composition(){
		$this->compositionAllOf();
		$this->compositionAnyOf();
		$this->compositionOneOf();
	}

	private function compositionAllOf(){
		if( is_null( $this->schema->allOf ) ){
			return;
		}

		foreach( $this->schema->allOf as $subSchema ){
			$valid = $subSchema->validate();

			if( ! $valid ){
				throw new CompositionException( "Value must match all sub-schemas." );
			}
		}
	}

	private function compositionAnyOf(){
		if( is_null( $this->schema->anyOf ) ){
			return;
		}

		foreach( $this->schema->anyOf as $subSchema ){
			$valid = $subSchema->validate();

			if( $valid ){
				break;
			}
		}

		throw new CompositionException( "Value must match at least one of the sub-schemas." );
	}

	private function compositionOneOf(){
		if( is_null( $this->schema->oneOf ) ){
			return;
		}

		$count = 0;

		foreach( $this->schema->oneOf as $subSchema ){
			$valid = $subSchema->validate();

			if( $valid ){
				$count++;
			}

			if( $count >= 2 ){
				throw new CompositionException( "Value must match exactly one of the sub-schemas." );
			}
		}

	}

	private function type(){
		$lookup = [
			Schema::STRING => [ "string" ],
			Schema::NULL => [ "NULL" ],
			Schema::NUMBER => [ "double", "integer" ],
			Schema::INTEGER => [ "integer" ],
			Schema::BOOLEAN => [ "boolean" ],
			Schema::ARRAY => [ "array" ],
			Schema::OBJECT => [ "array" ],
		];

		$phpType = gettype( $this->value );

		foreach( $this->schema->types as $allowedType ){
			if( in_array( $phpType, $lookup[ $allowedType ], true ) ){
				
				if( $phpType === "array" ){
					// Arrays & Objects have the same PHP type, double check them.
					$isNumeric = array_is_list( $this->value );

					if( $allowedType === Schema::ARRAY && $isNumeric ){
						return;
					}
					else if( $allowedType === Schema::OBJECT && ! $isNumeric ){
						return;
					}
				}
				else{
					return;
				}
			}
		}

		$list = collect( $this->schema->types )->map( fn( $e ) => "'$e'" )->join( ",", " or " );

		throw new InvalidTypeException( "Value must be of type $list." );
	}

	private function enum(){
		if( is_null( $this->schema->enum ) || count( $this->schema->enum ) === 0 ){
			return;
		}

		if( ! in_array( $this->value, $this->schema->enum, true ) ){
			$list = collect( $this->schema->enum )->map( fn( $e ) => "'$e'" )->join( ",", " or " );

			throw new OutOfEnumException( "Value must be one of $list." );
		}
	}

	private function const(){
		$const = $this->schema->const;

		if( is_null( $const ) ){
			return;
		}

		if( $this->value !== $const ){	
			throw new OutOfEnumException( "Value must be $const." );
		}
	}

	private function format(){
		$format = $this->schema->format;

		if( is_null( $format ) ){
			return;
		}

		$matches = Formats::matches( $format, $this->value );

		if( false === $matches ){
			throw new PatternMismatchException( "Value must be in the $format format." );
		}
	}

	private function string(){
		$this->stringRange();
		$this->stringPattern();
	}

	private function stringRange(){
		$min = $this->schema->minLength;
		$max = $this->schema->maxLength;

		if( $min && strlen( $this->value ) >= $min ) {
			throw new OutOfRangeException( "Value must be at least $min characters long." );
		}

		if( $max && strlen( $this->value ) < $max ) {
			throw new OutOfRangeException( "Value must be less than $max characters long." );
		}
	}

	private function stringPattern(){
		$pattern = $this->schema->pattern;

		if( is_null( $pattern ) ){
			return;
		}

		if( preg_match( $pattern, $this->value ) !== 1 ){
			throw new PatternMismatchException( "Value match the pattern: $pattern." );
		}
	}

	private function numeric(){
		$this->numericRange();
		$this->numericMultiple();
	}

	private function numericRange(){
		$min = $this->schema->min;
		$max = $this->schema->max;

		if( ! is_null( $min ) ){
			if( $this->schema->exclusiveMinimum ){
				if( ! ( $this->value > $min ) ){
					throw new OutOfRangeException( "Value must be greater than $min." );
				}
			}
			else{
				if( ! ( $this->value >= $min ) ){
					throw new OutOfRangeException( "Value must be greater than or equal to $min." );
				}
			}
		}

		if( ! is_null( $max ) ){
			if( $this->schema->exclusiveMaximum ){
				if( ! ( $this->value < $max ) ){
					throw new OutOfRangeException( "Value must be less than $max." );
				}
			}
			else{
				if( ! ( $this->value <= $max ) ){
					throw new OutOfRangeException( "Value must be less than or equal to $max." );
				}
			}
		}
	}

	private function numericMultiple(){
		$multipleOf = $this->schema->multipleOf;

		if( is_null( $multipleOf ) ){
			return;
		}

		if( $this->value % $multipleOf !== 0 ){
			throw new OutOfRangeException( "Value must be a multiple of $multipleOf." );
		}
	}

	private function array(){
		if( ! array_is_list( $this->value ) ){
			return;
		}

		$this->arrayItems();
		$this->arrayRange();
		$this->arrayUnique();

		/**
		 * # Note
		 * Not implemented: contains, minContains / maxContains, unevaluatedItems, prefixItems.
		 */
	}

	private function arrayItems(){
		$schema = $this->schema->items;

		if( is_null( $schema ) ){
			return;
		}

		$validator = new Validator( $schema );

		foreach( $this->value as $i => $item ){
			try{
				$validator->validate( $item );
			}
			catch( SchemaException $exception ){
				$nested = new NestedSchemaException( "Value at position [$i] does not match schema." );
				$nested->setPosition( "[$i]" );
				$nested->setChild( $exception );

				throw $nested;
			}
		}
	}

	private function arrayRange(){
		$min = $this->schema->minItems;
		$max = $this->schema->maxItems;
		$count = count( $this->value );

		if( $min && ! ( $count >= $min ) ) {
			throw new OutOfRangeException( "Value must contain at least $min items." );
		}

		if( $max && ! ( $count <= $max ) ) {
			throw new OutOfRangeException( "Value must contain at most $max items." );
		}
	}

	private function arrayUnique(){
		if( is_null( $this->schema->uniqueItems ) ){
			return;
		}

		// Warning: This will only compare the string cast of array items.
		$count = count( $this->value );
		$unique = count( array_unique( $this->value ) );

		if( $count !== $unique ){	
			throw new DuplicateItemException( "All items must be unique." );
		}
	}

	private function object(){
		if( array_is_list( $this->value ) ){
			return;
		}

		/**
		 * # Note
		 * Not implemented: patternProperties, unevaluatedProperties, propertyNames,
		 */

		 /**
		 * # Note
		 * required is implemented as an extension of sub-schemas, and not as a property of the object schema.
		 */

		$this->objectProperties();
		$this->objectRange();
	}

	private function objectProperties(){
		
		foreach( $this->value as $key => $item ){
			$schema = null;

			if( array_key_exists( $key, $this->schema->properties ) ){
				/**
				 * @var \WRD\Sleepy\Schema\Schema
				 */
				$schema = $this->schema->properties[ $key ];
			}
			else{
				$schema = $this->schema->additionalProperties;
			}

			if( is_null( $schema ) ){
				// Additional properties cam be anything if additionalProperties is not set.
				continue;
			}

			if( false === $schema ){
				// Additional properties are disallowed.
				throw new InvalidPropertyKeyException( "Value cannot contain unknown object keys." );
			}
	
			$validator = new Validator( $schema );

			try{
				$validator->validate( $item );
			}
			catch( SchemaException $exception ){
				$nested = new NestedSchemaException( "Value at position [$key] does not match schema." );
				$nested->setPosition( "[$key]" );
				$nested->setChild( $exception );

				throw $nested;
			}
		}
	}

	private function objectRange(){
		$min = $this->schema->minProperties;
		$max = $this->schema->maxProperties;
		$count = count( $this->value );

		if( $min && ! ( $count >= $min ) ) {
			throw new OutOfRangeException( "Value must contain at least $min properties." );
		}

		if( $max && ! ( $count <= $max ) ) {
			throw new OutOfRangeException( "Value must contain at most $max properties." );
		}
	}

	private function custom(){
		if( is_null( $this->schema->custom ) ){
			return;
		}

		$validator = FacadesValidator::make(
			[ 'value' => $this->value ], 
			[ 'value' => $this->schema->custom ]
		);

		if( $validator->fails() ) {
			$errors = $validator->errors()->get( 'value' );

			$exception = new CustomRuleException( $errors[0] );
			$exception->setAdditionalMessages( array_slice( $errors, 1 ) );

			throw $exception;
		}
	}
}