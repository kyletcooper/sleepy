<?php

namespace WRD\Sleepy\Fields\Filters;

enum Operator: string
{
    case Equals = "eq";
	case NotEquals = "neq";
	case Greater = "gt";
	case GreaterEquals = "gte";
	case Lesser = "lt";
	case LesserEquals = "lte";

	public function operand(): string {
		switch( $this->value ){
			case 'eq':
				return "=";
			case 'new':
				return "<>";
			case 'gt':
				return ">";
			case 'gte':
				return ">=";
			case 'lt':
				return "<";
			case 'lte':
				return "<=";
		}
	}
}
