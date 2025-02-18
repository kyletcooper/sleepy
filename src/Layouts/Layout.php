<?php

namespace WRD\Sleepy\Layout;

use JsonSerializable;
use WRD\Sleepy\Schema\Schema;

class Layout{
	public function schema(): Schema{
		return Schema::empty();
	}

	public function present( mixed $value ): JsonSerializable|array|bool|string|int|float{
		return json_encode( $value );
	}
}