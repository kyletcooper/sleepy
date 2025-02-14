<?php

namespace WRD\Sleepy\Api;

use Closure;
use Illuminate\Support\Facades\Route as FacadesRoute;
use WRD\Sleepy\Schema\Schema;
use WRD\Sleepy\Support\Tree\Node;

/**
 * @extends Node<WRD\Sleepy\Api\Group, WRD\Sleepy\Api\Endpoint>
 */
class Route extends ApiNode{
	/**
	 * @use Node<Group, Endpoint>
	 */
	use Node;

	protected Schema|Closure|null $schema = null;

	public function schema( Closure|Schema $schema ): static{
		$this->schema = $schema;

		return $this;
	}

	public function getSchema(): ?Schema{
		if( is_null( $this->schema ) ){
			return Schema::empty();
		}

		if( is_callable( $this->schema ) ){
			return call_user_func( $this->schema, $this );
		}

		return $this->schema;
	}

	function make(): void{
		$this->makeEndpointsDescription();

		parent::make();
	}
}