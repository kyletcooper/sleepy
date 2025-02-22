<?php

namespace WRD\Sleepy\Providers;

use Closure;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use WRD\Sleepy\Api\Generators\LoginGenerator;
use WRD\Sleepy\Api\Router;
use WRD\Sleepy\Console\Commands\GenerateMarkdownCommand;
use WRD\Sleepy\Console\Commands\ListEndpointsCommand;

final class SleepyServiceProvider extends ServiceProvider {
	public function register(): void {
		$this->mergeConfigFrom(
			__DIR__.'/../config/sleepy.php', 'sleepy'
		);

		$this->app->bind( 'apiRouter', function(){
			return new Router();
	    });

		Router::macro( 'model', function( string $model, ?Closure $callback = null ){
			return $model::registerApiRoutes( $callback );
		});

		Router::macro( 'login', function( string $path = '/session' ){
			$generator = new LoginGenerator($path);
			$generator->create();
		});
	}

	public function boot(): void {
		AboutCommand::add('WRD/Sleepy', fn () => ['Version' => '1.0.0']);

		if( $this->app->runningInConsole() ){
			$this->commands([
				ListEndpointsCommand::class,
				GenerateMarkdownCommand::class,
			]);
		}

		$this->publishes( [
			__DIR__.'/../config/sleepy.php' => config_path( 'sleepy.php' ),
		] );
	}
}