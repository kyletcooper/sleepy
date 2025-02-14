<?php

namespace WRD\Sleepy\Fields\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use WRD\Sleepy\Fields\Sorts\Sort;

class Sorts {
	static public function alphabetical( string $column ): Sort{
		return (new Sort())
			->column( $column );
	}

	static public function numeric( string $column ): Sort{
		return (new Sort())
			->column( $column );
	}

	static public function date( string $column ): Sort{
		return (new Sort())
			->column( $column );
	}

	static public function cases( string $column, array $cases ): Sort{
		return (new Sort())
			->column( $column )
			->query( function( Builder $builder, string $direction, Sort $sort ) use ( $cases ) {
				$model = $builder->getModel();
                $table = $model->getTable();
                $columns = Schema::getColumnListing($table);

                if( ! in_array( $sort->column, $columns, true ) ){
                    throw new InvalidArgumentException( "Column '" . $sort->column . "' cannot be used to sort." );
                }

                if( $direction === "desc" ){
                    $cases = array_reverse($cases);
                }

                $orderby = "CASE " . $sort->column;

                foreach($cases as $i => $case){
                    $orderby .= " WHEN " . DB::escape( $case->value ) . " THEN " . $i + 1;
                }

                $orderby .= " END";

                return $builder->orderByRaw($orderby);
			});
	}
}