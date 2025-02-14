<?php

namespace WRD\Sleepy\Fields\Links;

use Illuminate\Database\Eloquent\Model;
use WRD\Sleepy\Schema\Schema;

trait HasLinks{
	static public function links(): array{
		return [
			'self' => Link::self(),
			'collection' => Link::collection(),
		];
	}

	static public function getLinksAttributeName(){
		return "_links";
	}

	static public function bootHasLinks(){
		static::addHook( 'api.model.schema', function( Schema $schema ){
			if( ! config("sleepy.include_links_in_schema") ){
				return $schema;
			}
			
			$links = static::links();

			if( count( $links ) > 0 ){
				$schema->mergeIn( Schema::object( [
					static::getLinksAttributeName() => Schema::object( $links )
				] ) );
			}

			return $schema;
		});

		static::addHook( 'api.model.json', function( array $json, Model $model ){
			$links = collect( static::links() )
				->map( fn ( $attr, $key ) => $attr->getOutputValue( $key, $model ) )
				->all();

			if( count( $links ) > 0 ){
				$json[ static::getLinksAttributeName() ] = array_merge( $json[ static::getLinksAttributeName() ] ?? [], $links );
			}

			return $json;
		} );
	}
}