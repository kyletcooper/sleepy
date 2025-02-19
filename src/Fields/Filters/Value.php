<?php

namespace WRD\Sleepy\Fields\Filters;

use WRD\Sleepy\Fields\Filters\Operator;

class Value {
	public function __construct( public mixed $value, public string $name, public Operator $operator = Operator::Equals ) {}
}