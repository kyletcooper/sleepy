<?php

namespace WRD\Sleepy\Schema\Layouts;

use Closure;
use Illuminate\Database\Eloquent\Model;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Schema\Schema;

abstract class Layout {
	abstract public function getSchema(): Schema;

	/**
	 * @return Closure(mixed, ?string, ?Model, ?Field): mixed
	 */
	abstract public function getPresenter(): Closure;

	public function presentValue( mixed $value ): mixed{
		$presenter = $this->getPresenter();

		return call_user_func( $presenter, $value, null, null );
	}

	static public function present( mixed $value, ...$args ){
		$presenter = ( new static( ...$args ) )->getPresenter();

		return call_user_func( $presenter, $value, null, null );
	}
}