<?php

namespace WRD\Sleepy\Http\Exceptions;

class ApiFieldsException extends ApiException {
	protected array $errors;

	public function __construct( array $errors )
	{
		$this->errors = $errors;

		$message = collect( $errors )
			->map( fn( $e, $k ) => $k . ': ' . $e->getMessage() )
			->join( " " );

		parent::__construct( 400, $message );
	}

	public function getErrors(): array {
		return $this->errors;
	}

	public function jsonSerialize(): array
	{
		return [
			'success' => false,
			'error' => $this::class,
			'message' => $this->getMessage(),
			'fields' => $this->getErrors(),
		];
	}
}