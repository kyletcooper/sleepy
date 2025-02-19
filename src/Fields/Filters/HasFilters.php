<?php

namespace WRD\Sleepy\Fields\Filters;

use Illuminate\Database\Eloquent\Builder;
use WRD\Sleepy\Http\Requests\ApiRequest;

trait HasFilters{
	static public function filters(): array{
		return [];
	}

	static public function bootHasFilters(){
		static::addHook( 'api.model.fields.index', function( array $fields ){
			return array_merge( $fields, static::filters() );
		} );

		static::addHook( 'api.controller.index.query', function( Builder $query, ApiRequest $request ){
			foreach( $request->fields() as $name => $field ){
				if( ! is_a( $field, Filter::class ) ){
					continue;
				}
				
				$value = $request->values()->get( $name );

				if( is_null( $value ) || ( is_a( $value, Value::class ) && is_null( $value->value ) ) ){
					continue;
				}

				$query = $field->buildQuery( $query, $value, $name );
			}

			return $query;
		} );
	}
}