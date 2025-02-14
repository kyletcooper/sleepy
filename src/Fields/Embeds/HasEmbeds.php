<?php

namespace WRD\Sleepy\Fields\Embeds;

use Illuminate\Database\Eloquent\Model;
use WRD\Sleepy\Schema\Layouts\Link as LayoutsLink;
use WRD\Sleepy\Schema\Schema;

trait HasEmbeds{
	static public function embeds(): array{
		return [];
	}

	static public function embed(): Embed{
		return ( new Embed() )->model( static::class, static::hasEmbedsMergedAttributes() );
	}
	
	static public function getEmbedFields(): array{
		$embeds = static::embeds();
		$keys = array_keys( $embeds );

		/**
		 * We don't know the exact combination of keys & sub-keys,
		 * so we don't validate as an enum. We can provide some example
		 * values that we know are good though.
		 */
		return [
			static::getEmbedFieldsName() => Embed::array( Schema::string() )
				->default( [] )
				->describe( 'Include related models. You can use dot-notation to include embeds within embeds.' )
				->examples( $keys ),
		];
	}

	static public function getEmbedFieldsName(): string{
		return "_embed";
	}

	static public function getEmbedsAttributeName(): string{
		return "_embedded";
	}

	static public function getEmbedLinksAttributeName(): string{
		return "_links";
	}

	static public function hasEmbedsMergedAttributes(): bool{
		return static::getEmbedsAttributeName() === static::getEmbedLinksAttributeName();
	}

	static public function bootHasEmbeds(){
		static::addHook( ['api.model.fields.index', 'api.model.fields.create', 'api.model.fields.show', 'api.model.fields.update', 'api.model.fields.destroy'], function( array $fields ){
			return array_merge( $fields, static::getEmbedFields() );
		});

		static::addHook( 'api.model.schema', function( Schema $schema ){
			$embeds = static::embeds();

			if( config("sleepy.include_embeds_in_schema") ){
				$schema->mergeIn( Schema::object( [
					static::getEmbedsAttributeName() => Schema::object( $embeds )
				] ) );
			}

			if( static::hasEmbedsMergedAttributes() ){
				return $schema;
			}
			
			if( config("sleepy.include_links_in_schema") ){
				$links = collect( $embeds )->map(fn() => ( new LayoutsLink() )->getSchema() )->all();

				$schema->mergeIn( Schema::object( [
					static::getEmbedLinksAttributeName() => Schema::object( $links )->nullable()
				] ) );
			}

			return $schema;
		});

		static::addHook( 'api.model.json', function( array $json, Model $model ){
			$links = collect( static::embeds() )
				->map( function( $attr, $key ) use ( $model ){
					$related = $attr->getOutputValue( $key, $model, false );
					
					if( is_null( $related ) ){
						return null;
					}

					return LayoutsLink::present( $related->getSelfUrl(), [
						'embeddable' => true
					]);
				})
				->filter()
				->all();
				
			$embeds = collect( static::embeds() )
				->map( fn ( $attr, $key ) => $attr->getOutputValue( $key, $model ) )
				->filter()
				->all();

			if( count( $links ) > 0 ){
				$json[ static::getEmbedLinksAttributeName() ] = array_merge(
					$json[ static::getEmbedLinksAttributeName() ] ?? [],
					$links
				);
			}

			if( count( $embeds ) > 0 ){
				$json[ static::getEmbedsAttributeName() ] = array_merge(
					$json[ static::getEmbedsAttributeName() ] ?? [],
					$embeds
				);
			}

			return $json;
		} );
	}
}