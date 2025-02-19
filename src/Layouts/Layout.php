<?php

namespace WRD\Sleepy\Layouts;

use JsonSerializable;
use WRD\Sleepy\Schema\Schema;

abstract class Layout{
	public function schema(): Schema{
		return Schema::empty();
	}

	public function present( $value ): JsonSerializable|array|bool|string|int|float{
		return json_encode( $value );
	}
}