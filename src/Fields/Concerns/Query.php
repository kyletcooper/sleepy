<?php

namespace WRD\Sleepy\Fields\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;

trait Query {
	protected ?Closure $queryCallback;

	public function query( Closure $queryCallback ): static {
		$this->queryCallback = $queryCallback;

		return $this;
	}

	public function buildQuery( Builder $builder, mixed $value ): Builder{
		if( isset( $this->queryCallback ) ){
			return call_user_func( $this->queryCallback, $builder, $value, $this );
		}
		else{
			return $builder;
		}
	}
}