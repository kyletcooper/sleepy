<?php

namespace WRD\Sleepy\Fields\Sorts;

use Illuminate\Database\Eloquent\Builder;
use WRD\Sleepy\Fields\Concerns\BuildsQuery;
use WRD\Sleepy\Fields\Field;

class Sort extends Field{
	use BuildsQuery;

	public ?string $column = null;

	public function column( ?string $column ): static{
		$this->column = $column;

		return $this;
	}

	protected function defaultQuery( Builder $builder, mixed $direction ): Builder {
		return $builder->orderBy( $this->column, $direction );
	}
}