<?php

namespace WRD\Sleepy\Fields\Attributes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use WRD\Sleepy\Schema\Schema;

class Attr{
	use Macroable;

	static public function key(): Attribute{
		return Attribute::string()
			->readonly()
			->read( fn( string $name, Model $model ) => $model->getKey() );
	}

	static public function basename(): Attribute{
		return Attribute::string()
			->readonly()
			->read( fn( string $name, Model $model ) => strtolower( class_basename( $model::class ) ) );
	}

	static public function belongsTo( string $ownerModel, ?string $ownerKey = null ): Attribute {
		if( is_null( $ownerKey ) ){
			$ownerKey = ( new $ownerModel )->getKeyName();
		}

		return Attribute::create([Schema::INTEGER, Schema::STRING, Schema::NULL])
			->custom( 'exists:' . $ownerModel . ',' . $ownerKey )
			->writeonly()
			->write( function( Model $model, string $name, mixed $value ) use ( $ownerModel ) {
				$attachment = $ownerModel::findOrFail( $value );
				$model->$name()->associate( $attachment );

				return $model;
			});
	}

	public static function __callStatic($name, $arguments)
    {
        return Attribute::$name( ...$arguments );
    }
}