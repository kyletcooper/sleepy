<?php

namespace WRD\Sleepy\Schema\Layouts\Api;

use Closure;
use Illuminate\Support\Facades\Auth;
use WRD\Sleepy\Api\Group as ApiGroup;
use WRD\Sleepy\Api\Route as ApiRoute;
use WRD\Sleepy\Api\Route;
use WRD\Sleepy\Fields\Links\HasLinks;
use WRD\Sleepy\Schema\Layouts\Api\Route as LayoutsApiRoute;
use WRD\Sleepy\Schema\Layouts\Layout;
use WRD\Sleepy\Schema\Layouts\Link;
use WRD\Sleepy\Schema\Schema;

class Group extends Layout {
	public function getSchema(): Schema {
		return Schema::empty();
	}

	private function getNamespaces( ApiGroup $base ){
		return collect( $base->getChildren() )
			->filter( fn( $groupOrRoute ) => is_a( $groupOrRoute, ApiGroup::class ) )
			->filter( fn( ApiGroup $group ) => $group->isPublic() || Auth::check() )
			->mapWithKeys( fn( $group ) => [ $group->getPath() => Group::present( $group ) ] )
			->all();
	}

	private function getRoutes( ApiGroup $base ){
		return collect( $base->getChildren() )
			->filter( fn( $groupOrRoute ) => is_a( $groupOrRoute, ApiRoute::class ) )
			->filter( fn( Route $route ) => $route->isPublic() || Auth::check() )
			->mapWithKeys( fn( $route ) => [ $route->getPath() => LayoutsApiRoute::present( $route ) ] )
			->all();
	}

	public function getPresenter(): Closure
	{
		return function( ApiGroup $group ){
			return [
				'name' => $group->getName(),
				'description' => $group->getDescription(),
				'namespaces' => $this->getNamespaces( $group ),
				'routes' => $this->getRoutes( $group ),
				HasLinks::getLinksAttributeName() => [
					'self' => $group->getLinkJson(),
					'up' => $group->getParent()->getLinkJson(),
				]
			];
		};
	}
}