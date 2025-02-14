<?php

namespace WRD\Sleepy\Schema\Exceptions;

use InvalidArgumentException;
use JsonSerializable;
use WRD\Sleepy\Schema\Schema;

class SchemaException extends InvalidArgumentException implements JsonSerializable{
	protected ?Schema $schema = null;

	protected mixed $value = null;

	public function __construct( string $message )
	{	
		$this->message = $message;		
	}

	public function setSchema( Schema $schema ){
		$this->schema = $schema;
	}

	public function getSchema(){
		return $this->schema;
	}

	public function setValue( mixed $value ){
		$this->value = $value;
	}

	public function getValue(){
		return $this->value;
	}

	public function jsonSerialize(): array{
		return [
			'error' => $this::class,
			'message' => $this->getMessage(),
			'value' => $this->getValue(),
			'schema' => $this->getSchema(),
		];
	}
}