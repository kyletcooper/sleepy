<?php

namespace WRD\Sleepy\Layouts;

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

	/**
	 * @param array|string $value
	 */
	public function present( $value ): JsonSerializable|array|bool|string|int|float{
		$url = $value;
		$meta = [];

		if( is_array( $value ) ){
			$url = $value['href'];
			
			$meta = Arr::only( $value, [
				'href',
				'templated',
				'type',
				'deprecation',
				'name',
				'profile',
				'title',
				'hreflang',
				'embeddable',
			] );
		}

		return [
			'href' => $url,
			...$meta,
		];
	}
}