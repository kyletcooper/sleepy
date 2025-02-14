<?php

namespace WRD\Sleepy\Fields\Links;

use Illuminate\Database\Eloquent\Model;
use WRD\Sleepy\Fields\Concerns\Output;
use WRD\Sleepy\Schema\Layouts\Link as LayoutsLink;
use WRD\Sleepy\Schema\Schema;

class Link extends Schema{
	use Output;

	public function __construct( array $meta = [] )
	{
		parent::__construct();

		$this->layout( new LayoutsLink( $meta ) );
	}

	public static function to( string $href, array $meta = [] ): static{
		return ( new static( $meta ) )->getOutputVia( fn() => $href );
	}

	public static function self( array $meta = [] ): static{
		return ( new static( $meta ) )->getOutputVia( fn( $attr, $model ) => $model->getSelfUrl() );
	}

	public static function collection( array $meta = [] ): static{
		return ( new static( $meta ) )->getOutputVia( fn( $attr, $model ) => $model::class::getCollectionUrl() );
	}
	
	public static function for( Model $model, array $meta = [] ): static{
		return static::to( $model->getSelfUrl(), $meta );
	}

	public static function forCollection( string $model, array $meta = [] ): static{
		return static::to( $model::getCollectionUrl(), $meta );
	}
}