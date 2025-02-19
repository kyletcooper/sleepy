<?php

namespace WRD\Sleepy\Fields\Attributes;

use Illuminate\Database\Eloquent\Model;
use WRD\Sleepy\Fields\Concerns\Output;
use WRD\Sleepy\Fields\Concerns\Write;
use WRD\Sleepy\Fields\Field;

class Attribute extends Field{
	use Write {
		writeValue as protected parentWriteValue;
	}

	use Output {
		getOutputValue as protected parentGetOutputValue;
	}

	public ?string $alias = null;

	public function alias( string $name ): static{
		$this->alias = $name;

		return $this;
	}

	public function writeValue( Model $model, string $name, mixed $value ): Model{
		return $this->parentWriteValue( $model, $this->alias ?? $name, $value );
	}

	public function getOutputValue( string $name, mixed $model, bool $present = true ): mixed {
		return $this->parentGetOutputValue( $this->alias ?? $name, $model, $present );
	}
}