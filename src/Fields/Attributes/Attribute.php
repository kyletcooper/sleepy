<?php

namespace WRD\Sleepy\Fields\Attributes;

use Illuminate\Database\Eloquent\Model;
use WRD\Sleepy\Fields\Concerns\Output;
use WRD\Sleepy\Fields\Concerns\UpdatesModel;
use WRD\Sleepy\Fields\Field;

class Attribute extends Field{
	use UpdatesModel {
		updateModel as protected parentUpdateModel;
	}

	use Output {
		getOutputValue as protected parentGetOutputValue;
	}

	public ?string $alias = null;

	public function aliasFor( string $name ): static{
		$this->alias = $name;

		return $this;
	}

	protected function defaultUpdate( Model $model, string $name, mixed $value ): Model{
		$model->{$name} = $value;

		return $model;
	}

	public function updateModel( Model $model, string $name, mixed $value ): Model{
		return $this->parentUpdateModel( $model, $this->alias ?? $name, $value );
	}

	public function getOutputValue( string $name, mixed $model, bool $present = true ): mixed {
		return $this->parentGetOutputValue( $this->alias ?? $name, $model, $present );
	}
}