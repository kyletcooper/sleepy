<?php

namespace WRD\Sleepy\Api;

use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Http\Exceptions\ApiFieldsException;
use WRD\Sleepy\Http\Requests\ApiRequest;
use WRD\Sleepy\Schema\Exceptions\SchemaException;
use WRD\Sleepy\Schema\Schema;
use WRD\Sleepy\Support\Stack;
use WRD\Sleepy\Support\Tree\NodeType;

class Router{
	private Base $base;

	/**
	 * @var Stack<Group>
	 */
	private Stack $groupStack;

	/**
	 * @var Stack<Route>
	 */
	private Stack $routeStack;

	private ?ApiRequest $request = null;

	private ?Endpoint $endpoint = null;

	public function __construct()
	{
		$this->groupStack = new Stack();
		$this->routeStack = new Stack();
	}

	public function base( string $path, ?Closure $group ): Base{
		if( isset( $this->base ) ){
			throw new Exception( 'API already has a base.' );
		}

		$api = new Base( $path );

		$this->base = $api;

		if( ! is_null( $group ) ){
			call_user_func( $group );
		}

		$api->make();

		return $api;
	}

	public function getBase(): Base{
		return $this->base;
	}

	public function group( string $path, ?Closure $callback ): Group{
		if( ! isset( $this->base ) ){
			throw new Exception( 'API groups must be registered within a base.' );
		}

		$group = new Group( $path );

		if( $this->groupStack->isEmpty() ){
			$this->base->addChild( $group );
		}
		else{
			$this->groupStack->top()->addChild( $group );
		}

		if( ! is_null( $callback ) ){
			$this->groupStack->push( $group );
			
			call_user_func( $callback );
			
			$this->groupStack->pop();
		}

		return $group;
	}

	public function route( string $path, ?Closure $group ): Route{
		if( $this->groupStack->isEmpty() ){
			throw new Exception( 'API Routes must be registered within a group.' );
		}

		$route = new Route( $path );

		$this->groupStack->top()->addChild( $route );

		if( ! is_null( $group ) ){
			$this->routeStack->push( $route );

			call_user_func( $group );

			$this->routeStack->pop();
		}

		return $route;
	}

	public function endpoint( array|string $method, callable $action ): Endpoint{
		if( $this->routeStack->isEmpty() ){
			throw new Exception( 'API endpoints must be registered within a route.' );
		}

		if( ! is_array( $method ) ){
			$method = [ $method ];
		}

		$endpoint = new Endpoint( $method, $action );

		$this->routeStack->top()->addChild( $endpoint );

		return $endpoint;
	}

	public function model( string $model ){
		return $model::registerApiRoutes();
	}

	public function login( string $path = '/session' ){
		$schema = Schema::object([
			'authenticated' => Schema::boolean()
				->describe( 'Indicates if you are currently logged-in.' ),
			'id' => Schema::create( [ Schema::INTEGER, Schema::STRING ] )
				->nullable()
				->describe( 'The ID of the user you are logged-in as, or null if not logged-in.' )
		]);

		function getAuthObject(){
			return [
				'authenticated' => Auth::check(),
				'id' => Auth::id()
			];
		}

		return $this->route( $path, function(){
			$this->endpoint( 'GET', fn() => getAuthObject() )
			->describe( 'Check the status of your session.' )
			->responses( 200 );

			$this->endpoint( 'POST', function( ApiRequest $request ){
				if( Auth::attempt( $request->values()->all() ) ){
					$request->session()->regenerate();
				}

				return getAuthObject();
			})
			->fields([
				'email' => Field::string( 'email' )
					->required()
					->describe( 'The email address of the user you want to log in as.' ),
				'password' => Field::string()
					->required()
					->describe( 'The password for the user.' )
			])
			->describe( 'Begin an authenticated session by logging-in.' )
			->responses( 200, 400 );

			$this->endpoint( 'DELETE', function( ApiRequest $request ){
				if( Auth::check() ){
					Auth::logout();

					$request->session()->invalidate();
					$request->session()->regenerateToken();
				}

				return getAuthObject();
			})
			->describe( 'Log-out, invalidating the current session.' )
			->responses( 200 );
		})
		->describe( 'Manage a stateful session authentication' )
		->schema( $schema );
	}

	public function response( ?array $data = [], int $status = 200 ){
		return Response::json( $data, $status, [
			'Content-Type' => 'application/hal+json'
		]);
	}

	public function request(): ApiRequest{
		if( ! is_null( $this->request ) ){
			return $this->request;
		}

		$request = ApiRequest::createFrom( request() );

		['values' => $values, 'errors' => $errors] = $this->parseFields( $request->all() );
		$request->setValues( $values );
		$request->setEndpoint( $this->current() );

		if( count( $errors ) > 0 ){
			abort( new ApiFieldsException( $errors ) );
		}

		$this->request = $request;

		return $this->request;
	}

	public function current(): Endpoint{
		if( ! is_null( $this->endpoint ) ){
			return $this->endpoint;
		}

		$endpoint = $this->base->findFirst(
			fn( ApiNode $node ) => $node->getNodeType() === NodeType::Leaf && $node->matches( request() )
		);

		$this->endpoint = $endpoint;

		return $this->endpoint;
	}

	protected function parseFields( array $input ): array{
		$values = [];
		$errors = [];

		foreach( $this->current()->getFields() as $name => $field ){
			try{
				$values[ $name ] = $field->getInputValue( $name, $input );
			}
			catch( SchemaException $exception ) {
				$values[ $name ] = $field->default;
				$errors[ $name ] = $exception;
			}
		}

		return [ 'values' => $values, 'errors' => $errors ];
	}
}