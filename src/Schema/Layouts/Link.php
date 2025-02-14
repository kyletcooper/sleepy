<?php

namespace WRD\Sleepy\Schema\Layouts;

use Closure;
use WRD\Sleepy\Schema\Schema;

class Link extends Layout {
	public array $meta = [];

	public function __construct( array $meta = [] )
	{
		$this->meta = $meta;
	}

	public function getSchema(): Schema {
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

	public function getPresenter(): Closure {
		return function( string $url ){
			$ret = [
				'href' => $url,
				...$this->meta,
			];

			return $ret;
		};
	}
}