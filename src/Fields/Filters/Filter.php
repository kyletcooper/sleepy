<?php

namespace WRD\Sleepy\Fields\Filters;

use Illuminate\Database\Eloquent\Builder;
use WRD\Sleepy\Fields\Concerns\BuildsQuery;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Fields\Filters\Operator;
use WRD\Sleepy\Schema\Schema;

class Filter extends Field{
	use BuildsQuery;

	public ?array $operators = null;

	public ?string $column = null;

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

	public function column( ?string $column ): static{
		$this->column = $column;

		return $this;
	}

	protected function defaultQuery( Builder $builder, Value $value ): Builder{
		return $builder->where( $this->column, $value->operator->operand(), $value->value );
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

		return new Value( $value, $operator );
	}
}