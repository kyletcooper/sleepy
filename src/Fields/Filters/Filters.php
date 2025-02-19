<?php

namespace WRD\Sleepy\Fields\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\Rule;
use WRD\Sleepy\Fields\Filters\Filter;
use WRD\Sleepy\Fields\Filters\Value;
use WRD\Sleepy\Fields\Filters\Operator;
use WRD\Sleepy\Schema\Schema;

class Filters {
	use Macroable;

	static public function text(): Filter{
		return Filter::string()
			->operator( [ Operator::Equals, Operator::NotEquals ] );
	}

	static public function numeric(): Filter{
		return Filter::create( [ Schema::NUMBER, Schema::INTEGER ] )
			->operator( [ Operator::Equals, Operator::NotEquals, Operator::Greater, Operator::GreaterEquals, Operator::Lesser, Operator::LesserEquals ] );
	}

	static public function date(): Filter{
		return Filter::string( 'date-time' )
			->operator( [ Operator::Equals, Operator::NotEquals, Operator::Greater, Operator::GreaterEquals, Operator::Lesser, Operator::LesserEquals ] )
			->query( function( Builder $builder, Value $value, string $name ){
                return $builder->whereDate( $name, $value->operator->operand(), $value->value );
            });
	}

	static public function cases( array $cases ): Filter{
		return Filter::string()
			->operator( [ Operator::Equals, Operator::NotEquals ] )
			->custom( [ Rule::in( $cases ) ] );
	}

	static public function search(): Filter{
		return Filter::string()
			->operator( Operator::Equals )
			->query( function( Builder $builder, Value $value, string $name ) {
				return $builder->where( function( Builder $builder ) use ( $value, $name ){
					$columns = explode( ",", $name );

					foreach( $columns as $column ){
						$column = trim($column);

						$builder->orWhere($column, 'LIKE', '%' . $value->value . '%');
					}
				});
			});
	}

	static public function belongsTo( string $model, ?string $foreignKey = null ): Filter{
		if( is_null( $foreignKey ) ){
			$instance = new $model();
            $foreignKey = Str::snake( class_basename( $model ) ) . '_' . $instance->getKeyName();
        }

		return Filter::string()
			->operator( Operator::Equals )
			->alias( $foreignKey );
	}
}