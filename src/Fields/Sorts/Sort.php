<?php

namespace WRD\Sleepy\Fields\Sorts;

use Illuminate\Database\Eloquent\Builder;
use WRD\Sleepy\Fields\Concerns\Query;
use WRD\Sleepy\Fields\Field;

class Sort extends Field{
	use Query;

	public ?string $column = null;

	public function __construct( array|string $types = "" )
	{
		parent::__construct( $types );

		$this->queryCallback = fn( Builder $builder, mixed $direction ) =>
			$builder->orderBy( $this->column, $direction );
	}

	public function column( ?string $column ): static{
		$this->column = $column;

		return $this;
	}
}