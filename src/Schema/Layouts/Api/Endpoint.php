<?php

namespace WRD\Sleepy\Schema\Layouts\Api;

use Closure;
use WRD\Sleepy\Api\Endpoint as ApiEndpoint;
use WRD\Sleepy\Fields\Links\HasLinks;
use WRD\Sleepy\Schema\Layouts\Layout;
use WRD\Sleepy\Schema\Layouts\Link;
use WRD\Sleepy\Schema\Schema;

class Endpoint extends Layout {
	public function getSchema(): Schema {
		return Schema::empty();
	}

	public function getPresenter(): Closure
	{
		return function( ApiEndpoint $endpoint ){
			return [
				'name' => $endpoint->getName(),
				'description' => $endpoint->getDescription(),
				'methods' => $endpoint->getMethods(),
				'fields' => $endpoint->getFields(),
				'responses' => $endpoint->getResponseCodes(),
			];
		};
	}

	public static function fake( string $name, array $methods, string $description = "", array $fields = [], array $responses = [200, 401, 403] ){
		return [
			'name' => $name,
			'description' => $description,
			'methods' => $methods,
			'fields' => $fields,
			'responses' => $responses,
		];
	}
}