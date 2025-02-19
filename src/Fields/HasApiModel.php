<?php

namespace WRD\Sleepy\Fields;

use Illuminate\Support\Collection;
use WRD\Sleepy\Api\Endpoint;
use WRD\Sleepy\Api\Generators\ModelGenerator;
use WRD\Sleepy\Http\Controllers\ApiController;
use WRD\Sleepy\Schema\Schema;
use WRD\Sleepy\Support\HasHooks;

trait HasApiModel{
	use HasHooks;

	static Collection $endpoints;

	static public function getApiController() {
		return new ApiController( static::class );
	}

	public function toApi(): mixed {
		$json = [];

		$json = static::runHook( 'api.model.json', $json, $this );

		return $json;
	}

	static public function getRouteBase(): string {
		return '/' . strtolower( class_basename( static::class ) );
	}

	static public function getSchema(): Schema{
		$schema = Schema::object();

		$schema = static::runHook( 'api.model.schema', $schema );

		return $schema;
	}

	static public function getCollectionSchema(): Schema{
		$self = static::getSchema();

		$collection = Schema::array( $self );

		$collection = static::runHook( 'api.model.schema.collection', $collection );

		return $collection;
	}

	static public function getFields( string $action ){
		return static::runHook( "api.model.fields.$action", [] );
	}

	static public function setEndpoint( string $name, Endpoint $endpoint ): void{
		if( ! isset( static::$endpoints ) ){
			static::$endpoints = collect();
		}

		static::$endpoints[ $name ] = $endpoint;
	}

	static public function getEndpoints(): Collection{
		if( ! isset( static::$endpoints ) ){
			static::$endpoints = collect();
		}

		return static::$endpoints;
	}

	static public function getEndpoint( string $name ): ?Endpoint{
		return static::getEndpoints()->get( $name );
	}

	public function getSelfUrl(): ?string{
		$ep = static::getEndpoint( "show" );

		$params = [ 'model' => $this ] ;

		return $ep->getUrl( $params );
	}

	static public function getCollectionUrl(): ?string{
		$ep = static::getEndpoint( "index" );

		return $ep->getUrl();
	}

	static public function registerApiRoutes(){
		( new static() )->bootIfNotBooted();
    
		$generator = new ModelGenerator( static::class );
		
		$generator->create();
	}
}