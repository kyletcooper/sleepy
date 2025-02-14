<?php

namespace WRD\Sleepy\Fields\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;

trait BuildsQuery {
	public ?Closure $queryCallback;

	public function query( Closure $queryCallback ): static {
		$this->queryCallback = $queryCallback;

		return $this;
	}

	protected function defaultQuery( Builder $builder, mixed $value, self $field ): Builder{
		return $builder;
	}

	public function buildQuery( Builder $builder, mixed $value ): Builder{
		if( ! isset( $this->queryCallback ) ){
			return $this->defaultQuery( $builder, $value, $this );
		}

		return call_user_func( $this->queryCallback, $builder, $value, $this );
	}
}