<?php

namespace WRD\Sleepy\Schema\Exceptions;

class CustomRuleException extends SchemaException{
	protected array $additionalMessages = [];

	public function setAdditionalMessages( array $additionalMessages ){
		$this->additionalMessages = $additionalMessages;
	}

	public function getAdditionalMessages(){
		return $this->additionalMessages;
	}

	public function jsonSerialize(): array{
		$json = parent::jsonSerialize();

		return [
			...$json,
			'additional' => $this->additionalMessages,
		];
	}
}