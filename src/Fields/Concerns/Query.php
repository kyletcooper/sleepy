<?php

namespace WRD\Sleepy\Fields\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;

trait Query {
	use Touch;

	protected ?Closure $queryCallback;

	public function query( Closure $queryCallback ): static {
		$this->queryCallback = $queryCallback;

		return $this;
	}

	public function buildQuery( Builder $builder, mixed $value, string $name ): Builder{
		if( ! is_null( $this->alias ) ){
			$name = $this->alias;
		}

		if( isset( $this->queryCallback ) ){
			return call_user_func( $this->queryCallback, $builder, $value, $name, $this );
		}
		else{
			return $builder;
		}
	}
}