<?php

namespace WRD\Sleepy\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use WRD\Sleepy\Http\Exceptions\ApiNotFoundException;
use WRD\Sleepy\Http\Requests\ApiRequest;
use WRD\Sleepy\Support\Facades\API;

class ApiController{
	private string $model;

	public function __construct( string $model )
	{
		$this->model = $model;
	}

	public function assertModelMatches( $model ): void{
		if( is_null( $this->model ) || ! is_a( $model, $this->model ) ){
			abort( new ApiNotFoundException() );
		}

		return;
	}

	public function index(ApiRequest $request){
		$query = $this->model::query();
		$query = $this->model::runHook( 'api.controller.index.query', $query, $request );

		$json = $this->model::runHook( 'api.controller.index.json', null, $query, $request );

		if( ! is_null( $json ) ){
			// A hook has taken over the responsibilty of converting to JSON.
			return API::response( $json, 200 );
		}
		
		$json = $query
			->get()
			->map( fn( $model ) => $model->toApi() )
			->all();

			return API::response( $json, 200 );
	}

	public function create(ApiRequest $request){
		$model = new $this->model();
		$model = $this->model::runHook( 'api.controller.create.build', $model, $request );

		$model->save();

		$json = $model->toApi();

		return API::response( $json, 201 );
	}

	public function show(ApiRequest $request, Model $model){
		$this->assertModelMatches($model);

		$json = $model->toApi();

		return API::response( $json, 200 );
	}

	public function update(ApiRequest $request, Model $model){
				$this->assertModelMatches($model);

		$model = $this->model::runHook( 'api.controller.update.build', $model, $request );
		$model->save();

		$json = $model->toApi();

		return API::response( $json, 200 );
	}

	public function destroy(ApiRequest $request, Model $model){
				$this->assertModelMatches($model);

		$model->delete();

		return API::response( null, 204 );
	}
}