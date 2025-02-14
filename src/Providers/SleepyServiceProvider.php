<?php

namespace WRD\Sleepy\Providers;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use WRD\Sleepy\Api\Router;
use WRD\Sleepy\Console\Commands\GenerateMarkdownCommand;
use WRD\Sleepy\Console\Commands\ListEndpointsCommand;
use WRD\Sleepy\Http\Requests\ApiRequest;

final class SleepyServiceProvider extends ServiceProvider {
	public function register(): void {
		$this->mergeConfigFrom(
			__DIR__.'/../config/sleepy.php', 'sleepy'
		);

		$this->app->bind( 'apiRouter', function(){
			return new Router();
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