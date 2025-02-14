<?php

namespace WRD\Sleepy\Http\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Response;
use JsonSerializable;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException implements JsonSerializable, Responsable{
	public function __construct( int $status = 400, string $message = "" )
	{
		parent::__construct( $status, $message );
	}

	public function jsonSerialize(): array {
		return [
			'success' => false,
			'status' => $this->getStatusCode(),
			'message' => $this->getMessage(),
		];
	}

	public function toResponse($request){
		return Response::json( $this->jsonSerialize(), $this->getStatusCode(), $this->getHeaders() );
	}
}