<?php

namespace WRD\Sleepy\Fields\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use WRD\Sleepy\Fields\Sorts\Sort;

class Sorts {
	use Macroable;

	static public function alphabetical(): Sort{
		return new Sort();
	}

	static public function numeric(): Sort{
		return new Sort();
	}

	static public function date(): Sort{
		return new Sort();
	}

	static public function cases( array $cases ): Sort{
		return (new Sort())
			->query( function( Builder $builder, string $direction, string $name, Sort $sort ) use ( $cases ) {
				$model = $builder->getModel();
                $table = $model->getTable();
                $columns = Schema::getColumnListing($table);

                if( ! in_array( $name, $columns, true ) ){
                    throw new InvalidArgumentException( "Column '" . $name . "' cannot be used to sort." );
                }

                if( $direction === "desc" ){
                    $cases = array_reverse($cases);
                }

                $orderby = "CASE " . $name;

                foreach($cases as $i => $case){
                    $orderby .= " WHEN " . DB::escape( $case->value ) . " THEN " . $i + 1;
                }

                $orderby .= " END";

                return $builder->orderByRaw($orderby);
			});
	}
}