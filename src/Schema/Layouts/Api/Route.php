<?php

namespace WRD\Sleepy\Schema\Layouts\Api;

use Closure;
use WRD\Sleepy\Api\Base;
use WRD\Sleepy\Api\Endpoint as ApiEndpoint;
use WRD\Sleepy\Api\Group;
use WRD\Sleepy\Api\Route as ApiRoute;
use WRD\Sleepy\Fields\Links\HasLinks;
use WRD\Sleepy\Schema\Layouts\Layout;
use WRD\Sleepy\Schema\Layouts\Link;
use WRD\Sleepy\Schema\Schema;
use WRD\Sleepy\Support\Tree\NodeType;

class Route extends Layout {
	public function getSchema(): Schema {
		return Schema::empty();
	}

	private function getMethods( ApiRoute $route ){
		return collect( $route->getChildren() )
			->map( fn( ApiEndpoint $endpoint ) => $endpoint->getMethods() )
			->flatten()
			->unique()
			->all();
	}

	private function getRespones( ApiRoute $route ){
		return collect( $route->getChildren() )
			->map( fn( ApiEndpoint $endpoint ) => $endpoint->getResponseCodes() )
			->flatten()
			->unique()
			->sort()
			->values();
	}

	private function getEndpoints( ApiRoute $route ){
		return collect( $route->getChildren() )
			->mapWithKeys( function( ApiEndpoint $endpoint ){
				$keys = [];

				foreach( $endpoint->getMethods() as $method ){
					$keys[ $method ] = Endpoint::present( $endpoint );
				}

				return $keys;
			} )
			->all();
	}

	public function getPresenter(): Closure
	{
		return function( ApiRoute $route ){
			return [
				'name' => $route->getName(),
				'description' => $route->getDescription(),
				'methods' => $this->getMethods( $route ),
				'responses' => $this->getRespones( $route ),
				'endpoints' => $this->getEndpoints( $route ),
				'schema' => $route->getSchema(),
				HasLinks::getLinksAttributeName() => [
					'self' => $route->getLinkJson(),
					'up' => $route->getParent()->getLinkJson(),
				]
			];
		};
	}

	public static function fake( Base|Group|ApiRoute $target ){
		if( is_a( $target, ApiRoute::class ) ){
			return static::present( $target );
		}

		$data = [
			'name' => $target->getName(),
			'description' => $target->getDescription(),
			'methods' => ['GET'],
			'endpoints' => [
				'GET' => Endpoint::fake( $target->getName() . ".GET", ['GET'] )
			],
		];

		$links = [
			'self' => Link::present( $target->getUrl() ),
		];

		if( $target->getNodeType() !== NodeType::Root ) {
			$links['up'] = Link::present( $target->getParent()->getUrl() );
		};

		$data[ HasLinks::getLinksAttributeName() ] = $links;

		return $data;
	}
}