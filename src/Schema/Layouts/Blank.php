<?php

namespace WRD\Sleepy\Schema\Layouts;

use Closure;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Schema\Schema;

class Blank extends Layout {
	public function getSchema(): Schema {
		return Schema::empty();
	}

	public function getPresenter(): Closure
	{
		return function( mixed $value ){
			return json_encode( $value );
		};
	}
}