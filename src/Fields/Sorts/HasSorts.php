<?php

namespace WRD\Sleepy\Fields\Sorts;

use Illuminate\Database\Eloquent\Builder;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Http\Requests\ApiRequest;

trait HasSorts{
	static public function sorts(): array{
		return [];
	}

	static public function getSortFields(): array{
		$sorts = static::sorts();
		$order_by = array_keys( $sorts );

		return [
			static::getOrderByFieldName() => Sort::string()
				->enum( $order_by )
				->default( count( $order_by ) > 0 ? $order_by[0] : null )
				->describe( 'Controls how items are sorted.' ),

			static::getOrderFieldName() => Sort::string()
				->enum( array_column( Direction::cases(), 'value' ) )
				->default( Direction::Ascending->value )
				->describe( "Set the direction of sorting." ),
		];
	}

	static public function getOrderByFieldName(){
		return "order_by";
	}

	static public function getOrderFieldName(){
		return "order";
	}

	static public function bootHasSorts(){
		static::addHook( 'api.model.fields.index', function( array $fields ){
			return array_merge( $fields, static::getSortFields() );
		} );

		static::addHook( 'api.controller.index.query', function( Builder $query, ApiRequest $request ){
			$orderBy = $request->values()->get( static::getOrderByFieldName() );
			$order = $request->values()->get( static::getOrderFieldName() );

			$sorts = collect( static::sorts() );
			$sort = $sorts->get( $orderBy );

			if( is_null( $sort ) ){
				return $query;
			}

			$query = $sort->buildQuery( $query, $order, $orderBy );

			return $query;
		} );
	}
}