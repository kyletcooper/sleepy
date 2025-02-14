<?php

namespace WRD\Sleepy\Api;

use Illuminate\Support\Facades\Route;
use WRD\Sleepy\Schema\Layouts\Api\Group as ApiGroup;
use WRD\Sleepy\Support\Tree\Node;

class Group extends ApiNode{
	/**
	 * @use Node<Base, Route>
	 */
	use Node;

	function make(): void{
		$this->makeChildrenOverview( ApiGroup::class );
		$this->makeEndpointsDescription();

		parent::make();
	}
}