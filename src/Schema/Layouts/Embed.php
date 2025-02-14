<?php

namespace WRD\Sleepy\Schema\Layouts;

use Closure;
use Illuminate\Database\Eloquent\Model;
use WRD\Sleepy\Schema\Layouts\Link as LayoutsLink;
use WRD\Sleepy\Schema\Schema;
use WRD\Sleepy\Support\Facades\API;
use WRD\Sleepy\Support\Stack;
use WRD\Sleepy\Fields\Embeds\HasEmbeds;

class Embed extends Layout {
	public string $class;
	
	public bool $mergedWithLink = false;

	private static Stack $stack;

	public function __construct( string $class, bool $mergedWithLink = false )
	{
		$this->class = $class;
		$this->mergedWithLink = $mergedWithLink;
	}

	static public function pushStack( string $item ){
		if( ! isset( static::$stack ) ){
			static::$stack = new Stack();
		}

		static::$stack->push( $item );
	}

	static public function shouldInclude(){
		$request = API::request();
		$include = $request->values()->get( HasEmbeds::getEmbedFieldsName() );
		$name = static::$stack->values()->reverse()->join(".");

		return in_array( $name, $include, true );
	}

	public function getSchema(): Schema {
		if( $this->mergedWithLink ){
			return Schema::empty()
				->oneOf([
					(new LayoutsLink)->getSchema(),
					$this->class::getSchema(),
				]);
		}

		return $this->class::getSchema()->nullable();
	}

	public function getPresenter(): Closure {
		return function( ?Model $value, string $attribute ){
			if( is_null( $value ) ){
				return null;
			}

			static::pushStack( $attribute );

			$json = null;

			if( static::shouldInclude() ){
				$json = $value->toApi();
			}
			else if( $this->mergedWithLink ){
				$json = LayoutsLink::present( $value->getSelfUrl(), [ 'embeddable' => true ]);
			}

			static::$stack->pop();

			return $json;
		};
	}
}