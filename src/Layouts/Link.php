<?php

namespace WRD\Sleepy\Layout;

use Illuminate\Support\Arr;
use JsonSerializable;
use WRD\Sleepy\Schema\Schema;

class Link extends Layout{
	public function schema(): Schema{
		return Schema::object([
			'href' => Schema::string( 'url' ),
			'templated' => Schema::boolean()->nullable(),
			'type' => Schema::string()->nullable(),
			'deprecation' => Schema::boolean()->nullable(),
			'name' => Schema::string()->nullable(),
			'profile' => Schema::string()->nullable(),
			'title' => Schema::string()->nullable(),
			'hreflang' => Schema::string()->nullable(),

			/**
			 * # Note: 'embeddable' is not part of the HAL spec.
			 */
			'embeddable' => Schema::boolean()->nullable(),
		]);
	}

	public function present( mixed $value ): JsonSerializable|array|bool|string|int|float{
		$url = $value;
		$meta = [];

		if( is_array( $value ) ){
			$url = $value['href'];
			$meta = Arr::except( $value, ['href'] );
		}

		return [
			'href' => $url,
			...$meta,
		];
	}
}