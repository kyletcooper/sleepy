<?php

namespace WRD\Sleepy\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use WRD\Sleepy\Api\Endpoint;

class ApiRequest extends Request {
	protected ?Endpoint $endpoint = null;

	protected array $values = [];

	public function setValues( array $values ){
		$this->values = $values;
	}

	public function setEndpoint( Endpoint $endpoint ){
		$this->endpoint = $endpoint;
	}

	public function endpoint(): ?Endpoint{
		return $this->endpoint;
	}

	public function fields(): Collection {
		if( is_null( $this->endpoint() ) ){
			return collect();
		}

		return collect( $this->endpoint()->getFields() );
	}

	public function values(): Collection {
		return collect( $this->values );
	}
}