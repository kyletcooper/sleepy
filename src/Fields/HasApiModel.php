<?php

namespace WRD\Sleepy\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use WRD\Sleepy\Api\Endpoint;
use WRD\Sleepy\Http\Controllers\ApiController;
use WRD\Sleepy\Http\Middleware\SpecifiedBinding;
use WRD\Sleepy\Http\Requests\ApiRequest;
use WRD\Sleepy\Schema\Schema;
use WRD\Sleepy\Support\Facades\API;
use WRD\Sleepy\Support\HasHooks;

trait HasApiModel{
	use HasHooks;

	static Collection $endpoints;

	static public function controller() {
		return new ApiController( static::class );
	}

	public function toApi(): mixed {
		$json = [];

		$json = static::runHook( 'api.model.json', $json, $this );

		return $json;
	}

	static public function getRouteBase(): string {
		return strtolower( class_basename( static::class ) );
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

	static public function getIndexFields(){
		return static::runHook( 'api.model.fields.index', [] );
	}

	static public function getCreateFields(){
		return static::runHook( 'api.model.fields.create', [] );
	}

	static public function getShowFields(){
		return static::runHook( 'api.model.fields.show', [] );
	}

	static public function getUpdateFields(){
		return static::runHook( 'api.model.fields.update', [] );
	}

	static public function getDestroyFields(){
		return static::runHook( 'api.model.fields.destroy', [] );
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

		$base = static::getRouteBase();

		// Collection
		API::route( "/$base", function() { 
				
			$index = API::endpoint( 'GET', [static::controller(), "index" ] )
				->auth( fn() => Gate::allows( 'viewAny', static::class ) )
				->fields( static::getIndexFields() )
				->responses( 200, 400, 401, 403 )
				->describe( 'Show the collection of models.' );
			
			$create = API::endpoint( 'POST', [static::controller(), "create" ] )
				->auth( fn() => Gate::allows( 'create', static::class ) )
				->fields( static::getCreateFields() )
				->responses( 201, 400, 401, 403 )
				->describe( 'Create a new model.' );
			
			static::setEndpoint( 'index', $index );
			static::setEndpoint( 'create', $create );
			
		})->schema( fn() => static::getSchema() );

		// Self
		API::route( "/$base/{model}", function() { 
			
			$show = API::endpoint( 'GET', [static::controller(), "show" ] )
				->auth( fn( ApiRequest $req, Model $model ) => Gate::allows( 'view', $model ) )
				->fields( static::getShowFields() )
				->responses( 200, 400, 401, 403, 404 )
				->describe( 'Show the model.' );
			
			$update = API::endpoint( 'POST', [static::controller(), "update" ] )
				->auth( fn( ApiRequest $req, Model $model ) => Gate::allows( 'update', $model ) )
				->fields( static::getUpdateFields() )
				->responses( 200, 400, 401, 403, 404 )
				->describe( 'Update the model.' );
			
			$delete = API::endpoint( 'DELETE', [static::controller(), "destroy" ] )
				->auth( fn( ApiRequest $req, Model $model ) => Gate::allows( 'delete', $model ) )
				->fields( static::getDestroyFields() )
				->responses( 204, 400, 401, 403, 404 )
				->describe( 'Delete the model.' );
			
			static::setEndpoint( 'show', $show );
			static::setEndpoint( 'update', $update );
			static::setEndpoint( 'delete', $delete );
			
		})
			->middleware( SpecifiedBinding::class . ":model," . static::class )
			->schema( fn() => static::getSchema() );
	}
}