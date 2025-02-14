<?php

namespace WRD\Sleepy\Schema\Layouts\Api;

use Closure;
use Illuminate\Support\Facades\Auth;
use WRD\Sleepy\Api\Base as ApiBase;
use WRD\Sleepy\Schema\Layouts\Api\Group as ApiGroup;
use WRD\Sleepy\Api\Group;
use WRD\Sleepy\Fields\Links\HasLinks;
use WRD\Sleepy\Schema\Layouts\Layout;
use WRD\Sleepy\Schema\Schema;

class Base extends Layout {
	public function getSchema(): Schema {
		return Schema::empty();
	}

	private function getNamespaces( ApiBase $base ){
		return collect( $base->getChildren() )
			->filter( fn( Group $group ) => $group->isPublic() || Auth::check() )
			->mapWithKeys( fn( $group ) => [ $group->getPath() => ApiGroup::present( $group ) ] )
			->all();
	}

	public function getPresenter(): Closure
	{
		return function( ApiBase $base ){
			return [
				'name' => $base->getName(),
				'description' => $base->getDescription(),
				'namespaces' => $this->getNamespaces( $base ),
				HasLinks::getLinksAttributeName() => [
					'self' => $base->getLinkJson()
				]
			];
		};
	}
}