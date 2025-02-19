<?php

namespace WRD\Sleepy\Fields\Links;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;

class Links {
	use Macroable;

	public static function to( string $href, array $meta = [] ): Link{
		return ( new Link() )->read( fn() => ['href' => $href, ...$meta] );
	}

	public static function self( array $meta = [] ): Link{
		return ( new Link() )->read( fn( $attr, $model ) => ['href' => $model->getSelfUrl(), ...$meta] );
	}

	public static function collection( array $meta = [] ): Link{
		return ( new Link() )->read( fn( $attr, $model ) => ['href' => $model::class::getCollectionUrl(), ...$meta] );
	}
	
	public static function for( Model $model, array $meta = [] ): Link{
		return static::to( $model->getSelfUrl(), $meta );
	}

	public static function forCollection( string $model, array $meta = [] ): Link{
		return static::to( $model::getCollectionUrl(), $meta );
	}
}