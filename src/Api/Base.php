<?php

namespace WRD\Sleepy\Api;

use Illuminate\Support\Facades\Route;
use WRD\Sleepy\Http\Exceptions\ApiNotFoundException;
use WRD\Sleepy\Schema\Layouts\Api\Base as ApiBase;
use WRD\Sleepy\Support\Tree\Root;

class Base extends ApiNode{
	/**
	 * @use Root<Group>
	 */
	use Root;

	function make(): void{
		$this->makeChildrenOverview( ApiBase::class );
		$this->makeEndpointsDescription();

		parent::make();

		Route::any( $this->getPath() . '/{fallbackPlaceholder}', fn() => abort( new ApiNotFoundException() ) )
			->where( 'fallbackPlaceholder', '.*' )
			->name( $this->getName() . '.404' );
	}
}