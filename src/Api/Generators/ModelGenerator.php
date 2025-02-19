<?php

namespace WRD\Sleepy\Api\Generators;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use WRD\Sleepy\Api\Endpoint;
use WRD\Sleepy\Http\Middleware\SpecifiedBinding;
use WRD\Sleepy\Http\Requests\ApiRequest;
use WRD\Sleepy\Schema\Schema;
use WRD\Sleepy\Support\Facades\API;

class ModelGenerator extends Generator{
	public string $class;

	public string $path;
	public mixed $controller;
	public Schema $schema;

	public function __construct( string $class )
	{
		$this->class = $class;
		
		$this->path = $class::getRouteBase();
		$this->controller = $class::getApiController();
		$this->schema = $class::getSchema();
	}

	public function setEndpoint( string $name, Endpoint $endpoint ){
		$this->class::setEndpoint( $name, $endpoint );
	}

	public function create(){
		// Collection
		API::route( $this->path, function() { 
				
			$index = API::endpoint( 'GET', [$this->controller, "index" ] )
				->auth( fn() => Gate::allows( 'viewAny', $this->class ) )
				->fields( $this->class::getFields( 'index' ) )
				->responses( 200, 400, 401, 403 )
				->describe( 'Show the collection of models.' );
			
			$create = API::endpoint( 'POST', [$this->controller, "create" ] )
				->auth( fn() => Gate::allows( 'create', static::class ) )
				->fields( $this->class::getFields( 'create' ) )
				->responses( 201, 400, 401, 403 )
				->describe( 'Create a new model.' );
			
			$this->setEndpoint( 'index', $index );
			$this->setEndpoint( 'create', $create );
			
		})
			->schema( fn() => $this->schema );

		// Self
		API::route( "$this->path/{model}", function() { 
			
			$show = API::endpoint( 'GET', [$this->controller, "show" ] )
				->auth( fn( ApiRequest $req, Model $model ) => Gate::allows( 'view', $model ) )
				->fields( $this->class::getFields( 'show' ) )
				->responses( 200, 400, 401, 403, 404 )
				->describe( 'Show the model.' );
			
			$update = API::endpoint( 'POST', [$this->controller, "update" ] )
				->auth( fn( ApiRequest $req, Model $model ) => Gate::allows( 'update', $model ) )
				->fields( $this->class::getFields( 'update' ) )
				->responses( 200, 400, 401, 403, 404 )
				->describe( 'Update the model.' );
			
			$delete = API::endpoint( 'DELETE', [$this->controller, "destroy" ] )
				->auth( fn( ApiRequest $req, Model $model ) => Gate::allows( 'destroy', $model ) )
				->fields( $this->class::getFields( 'destroy' ) )
				->responses( 204, 400, 401, 403, 404 )
				->describe( 'Delete the model.' );
			
			$this->setEndpoint( 'show', $show );
			$this->setEndpoint( 'update', $update );
			$this->setEndpoint( 'delete', $delete );
			
		})
			->middleware( SpecifiedBinding::class . ":model," . static::class )
			->schema( fn() => $this->schema );
	}
}