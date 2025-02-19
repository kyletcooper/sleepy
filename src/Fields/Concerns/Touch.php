<?php

namespace WRD\Sleepy\Fields\Concerns;

trait Touch {
	public ?string $alias = null;

	public function alias( string $name ): static{
		$this->alias = $name;

		return $this;
	}
}