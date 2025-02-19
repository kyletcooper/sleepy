<?php

namespace WRD\Sleepy\Fields\Sorts;

use Illuminate\Database\Eloquent\Builder;
use WRD\Sleepy\Fields\Concerns\Query;
use WRD\Sleepy\Fields\Field;

class Sort extends Field{
	use Query;

	public function __construct( array|string $types = "" )
	{
		parent::__construct( $types );

		$this->queryCallback = fn( Builder $builder, mixed $direction, string $name ) =>
			$builder->orderBy( $name, $direction );
	}
}