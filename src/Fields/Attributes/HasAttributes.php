<?php

namespace WRD\Sleepy\Fields\Attributes;

use Illuminate\Database\Eloquent\Model;
use WRD\Sleepy\Http\Requests\ApiRequest;
use WRD\Sleepy\Schema\Schema;

trait HasAttributes{
	static public function attributes(): array{
		return [
			'id' => Attr::key(),
			'type' => Attr::basename(),
		];
	}
	
	static public function bootHasAttributes(){
		static::addHook( ['api.controller.create.build', 'api.controller.update.build'], function( Model $model, ApiRequest $request ){
			foreach( $request->fields() as $name => $field ){
				if( ! is_a( $field, Attribute::class ) ){
					continue;
				}
				
				$value = $request->values()->get( $name );

				if( ! is_null( $value ) ){
					$model = $field->updateModel( $model, $name, $value );
				}
			}

			return $model;
		} );

		static::addHook( ['api.model.fields.create'], function( array $fields ){
			$attrs = collect( static::attributes() )
				->filter( fn( $attr ) => ! $attr->readOnly )
				->all();

			return array_merge( $fields, $attrs );
		} );

		static::addHook( ['api.model.fields.update'], function( array $fields ){
			$attrs = collect( static::attributes() )
				->map( fn( $attr ) => $attr->optional() )
				->filter( fn( $attr ) => ! $attr->readOnly )
				->all();

			return array_merge( $fields, $attrs );
		} );

		static::addHook( 'api.model.schema', function( Schema $schema ){
			$attributes = collect( static::attributes() )
				->filter( fn( $attr ) => ! $attr->writeOnly )
				->all();

			$schema->mergeIn( Schema::object( $attributes ) );

			return $schema;
		});

		static::addHook( 'api.model.json', function( array $json, Model $model ){
			$attributes = collect( $model::attributes() )
				->filter( fn( $attr ) => ! $attr->writeOnly )
				->map( fn( $attr, $name ) => $attr->getOutputValue( $name, $model ) )
				->all();

			return array_merge( $attributes, $json );
		} );
	}
}