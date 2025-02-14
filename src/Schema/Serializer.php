<?php

namespace WRD\Sleepy\Schema;

class Serializer{
	private ?Schema $schema;

	private ?array $data;

	public function serialize( Schema $schema ): object{
		$this->schema = $schema;
		$this->data = [];

		$this->base();
		$this->types();

		$result = $this->data;

		$this->schema = null;
		$this->data = null;

		return (object) $result;
	}

	private function addNullableProps( array $props ){
		foreach( $props as $prop ){
			if( ! is_null( $this->schema->{$prop} ) ){
				$this->data[ $prop ] = $this->schema->{$prop};
			}
		}
	}

	private function base(){
		if( count( $this->schema->types ) > 1 ){
			$this->data[ 'type' ] = $this->schema->types;
		}
		else if( count( $this->schema->types ) > 0 ){
			$this->data[ 'type' ] = $this->schema->types[0];
		}

		$this->addNullableProps([
			'title',
			'description',
			'readOnly',
			'writeOnly',
			'examples',
			'deprecated',
			
			'default',
			'required',
			'const',
			'enum',

			'allOf',
			'anyOf',
			'oneOf',
		]);
	}

	private function types(){
		if( $this->schema->hasType( Schema::STRING ) ){
			$this->string();
		}

		if( $this->schema->hasType( Schema::NUMBER ) || $this->schema->hasType( Schema::INTEGER ) ){
			$this->numeric();
		}

		if( $this->schema->hasType( Schema::ARRAY ) ){
			$this->array();
		}

		if( $this->schema->hasType( Schema::OBJECT ) ){
			$this->object();
		}
	}

	private function string(){
		$this->addNullableProps([
			'format',
			'pattern'
		]);
	}

	private function numeric(){
		$this->addNullableProps([
			'min',
			'exclusiveMinimum',
			'max',
			'exclusiveMaximum',
			'multipleOf',
		]);
	}

	private function array(){
		$this->addNullableProps([
			'items',
			'minItems',
			'maxItems',
			'uniqueItems',
		]);
	}

	private function object(){
		if( ! is_null( $this->schema->properties ) ){
			$this->data['properties'] = $this->schema->properties;
		} else {
			$this->data['properties'] = [];
		}

		$this->addNullableProps( [ 'additionalProperties' ] );
	}
}