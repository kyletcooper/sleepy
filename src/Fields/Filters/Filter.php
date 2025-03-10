<?php

namespace WRD\Sleepy\Fields\Filters;

use Illuminate\Database\Eloquent\Builder;
use WRD\Sleepy\Fields\Concerns\Query;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Fields\Filters\Operator;
use WRD\Sleepy\Schema\Schema;

class Filter extends Field{
	use Query;

	public ?array $operators = null;

	public function __construct( array|string $types = "" )
	{
		parent::__construct( $types );

		$this->queryCallback = fn( Builder $builder, mixed $value, string $name ) =>
			$builder->where( $name, $value->operator->operand(), $value->value );
	}

	public function operator( Operator|array $operators ): static{
		if( ! is_array( $operators ) ){
			$operators = [ $operators ];
		}

		$this->operators = $operators;

		if( count( $this->operators ) > 1 ){
			// Multiple operators are supported, update the schema.
			$types = collect( $this->types )
				->push( Schema::OBJECT )
				->unique();

			$properties = collect( $operators )
				->mapWithKeys( fn( $op ) => [
					$op->value => $this->exportSchema()->describe( $op->name )
				] );

			$this->type( $types->all() );
			$this->properties( $properties->all() );
		}

		return $this;
	}

	public function getInputValue(string $name, array $values): Value {
		$raw = parent::getInputValue( $name, $values );

		$operator = Operator::Equals;
		$value = $raw;

		if( is_array( $raw ) ){
			$keys = array_keys( $raw );
			$key = $keys[0];
			
			$operator = Operator::from( $key );
			$value = $raw[ $key ];
		}

		return new Value( $value, $name, $operator );
	}
}