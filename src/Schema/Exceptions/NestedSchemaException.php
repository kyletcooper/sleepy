<?php

namespace WRD\Sleepy\Schema\Exceptions;

class NestedSchemaException extends SchemaException{
	private ?SchemaException $child = null;

	private ?string $position = null;

	public function setChild( SchemaException $child ){
		$this->child = $child;
	}

	public function getChild(){
		return $this->child;
	}

	public function setPosition( string $position ){
		$this->position = $position;
	}

	public function getPosition(){
		return $this->position;
	}

	public function jsonSerialize(): array{
		$json = parent::jsonSerialize();
		$pos = $this->getPosition();

		return [
			...$json,
			'fields' => [
				$pos => $this->getChild()
			],
		];
	}
}