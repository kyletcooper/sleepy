<?php

namespace WRD\Sleepy\Api\Generators;

use Illuminate\Support\Facades\Auth;
use WRD\Sleepy\Api\Route;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Http\Requests\ApiRequest;
use WRD\Sleepy\Schema\Schema;
use WRD\Sleepy\Support\Facades\API;

class LoginGenerator extends Generator{
	public string $path;

	public function __construct( string $path )
	{
		$this->path = $path;		
	}

	public function create(): Route{
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

		return API::route( $this->path, function() {
			API::endpoint( 'GET' )
				->action(fn() => getAuthObject())
				->describe( 'Check the status of your session.' )
				->responses( 200 );

			API::endpoint( 'POST' )
				->fields([
					'email' => Field::string( 'email' )
						->required()
						->describe( 'The email address of the user you want to log in as.' ),
					'password' => Field::string()
						->required()
						->describe( 'The password for the user.' )
				])
				->action( function( ApiRequest $request ){
					if( Auth::attempt( $request->values()->all() ) ){
						$request->session()->regenerate();
					}

					return getAuthObject();
				})
				->describe( 'Begin an authenticated session by logging-in.' )
				->responses( 200, 400 );

			API::endpoint( 'DELETE' )
				->action(function( ApiRequest $request ){
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
}