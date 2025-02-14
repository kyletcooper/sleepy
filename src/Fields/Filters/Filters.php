<?php

namespace WRD\Sleepy\Fields\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use WRD\Sleepy\Fields\Filters\Filter;
use WRD\Sleepy\Fields\Filters\Value;
use WRD\Sleepy\Fields\Filters\Operator;
use WRD\Sleepy\Schema\Schema;

class Filters {
	static public function text( string $column ): Filter{
		return Filter::string()
			->operator( [ Operator::Equals, Operator::NotEquals ] )
			->column( $column );
	}

	static public function numeric( string $column ): Filter{
		return Filter::create( [ Schema::NUMBER, Schema::INTEGER ] )
			->operator( [ Operator::Equals, Operator::NotEquals, Operator::Greater, Operator::GreaterEquals, Operator::Lesser, Operator::LesserEquals ] )
			->column( $column );
	}

	static public function date( string $column ): Filter{
		return Filter::string( 'date-time' )
			->operator( [ Operator::Equals, Operator::NotEquals, Operator::Greater, Operator::GreaterEquals, Operator::Lesser, Operator::LesserEquals ] )
			->column( $column )
			->query( function( Builder $builder, Value $value, Filter $filter ){
                return $builder->whereDate( $filter->column, $value->operator->operand(), $value->value );
            });
	}

	static public function cases( string $column, array $cases ): Filter{
		return Filter::string()
			->operator( [ Operator::Equals, Operator::NotEquals ] )
			->custom( [ Rule::in( $cases ) ] )
			->column( $column );
	}

	static public function search( string $columns ): Filter{
		return Filter::string()
			->operator( Operator::Equals )
			->column( $columns )
			->query( function( Builder $builder, Value $value, Filter $filter ) {
				return $builder->where( function( Builder $builder ) use ( $value, $filter ){
					$columns = explode( ",", $filter->column );

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
			->column( $foreignKey );
	}
}