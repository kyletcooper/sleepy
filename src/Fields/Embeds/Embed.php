<?php

namespace WRD\Sleepy\Fields\Embeds;

use WRD\Sleepy\Fields\Concerns\Output;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Layouts\Link;
use WRD\Sleepy\Schema\Schema;
use WRD\Sleepy\Support\Facades\API;
use WRD\Sleepy\Support\Stack;

class Embed extends Field{
	use Output {
		Output::getOutputValue as protected parentGetOutputValue;
	}

	protected string $model;

	static protected Stack $stack;

	public function __construct( string $model )
	{
		parent::__construct();

		$this->model = $model;

		$this->applySchema();
	}

	protected function isMergedWithLink(){
		return $this->model::hasEmbedsMergedAttributes();
	}

	protected function applySchema(){
		$schema = $this->model::getSchema()->nullable();

		if( $this->isMergedWithLink() ){
			$schema = Schema::empty()
				->oneOf([
					(new Link)->schema(),
					$schema,
				]);
		}

		$this->importSchema( $schema );
	}

	protected function shouldInclude(){
		$request = API::request();
		$name = static::$stack->values()->reverse()->join(".");
		$include = $request->values()->get( $this->model::getEmbedFieldsName() );

		return in_array( $name, $include, true );
	}

	public function getOutputValue(string $name, mixed $model, bool $applyLayout = true): mixed
	{
		if( ! isset( static::$stack ) ){
			static::$stack = new Stack();
		}

		static::$stack->push( $name );

		$value = $this->parentGetOutputValue( $name, $model, $applyLayout );
		$output = null;

		if( $applyLayout ){
			if( $this->shouldInclude() && ! is_null( $value ) ){
				$output = $value->toApi();
			}
			else if( $this->isMergedWithLink() && ! is_null( $value ) ){
				$output = (new Link)->present([
					'href' => $value->getSelfUrl(),
					'embeddable' => true
				]);
			}
		}
		else{
			$output = $value;
		}

		static::$stack->pop();

		return $output;
	}
}