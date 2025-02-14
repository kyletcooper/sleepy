<?php

namespace WRD\Sleepy\Fields\Pagination;

use Illuminate\Database\Eloquent\Builder;
use WRD\Sleepy\Fields\Pagination\Pagination as PaginationField;
use WRD\Sleepy\Http\Requests\ApiRequest;
use WRD\Sleepy\Schema\Layouts\Pagination;
use WRD\Sleepy\Support\HasHooks;

trait HasPagination{
	use HasHooks;

	static public function getPaginationFields(): array{
		return [
			static::getPerPageFieldName() => PaginationField::integer()
				->default( 10 )
				->describe( 'Choose how many items appear on each page of the query.' )
				->min(0)
				->max(99),

			static::getPageFieldName() => PaginationField::integer()
				->default( 1 )
				->describe( 'The page for this query.' )
				->min(0),
		];
	}

	static public function getPerPageFieldName(){
		return "per_page";
	}

	static public function getPageFieldName(){
		return "page";
	}

	static public function bootHasPagination(){
		static::addHook( 'api.model.fields.index', function( array $fields ){
			return array_merge( $fields, static::getPaginationFields() );
		} );

		static::addHook( 'api.controller.index.json', function( mixed $json, Builder $query, ApiRequest $request ){
			$perPage = $request->values()->get( static::getPerPageFieldName() );
			$page = $request->values()->get( static::getPageFieldName() );

			$paginator = $query
				->paginate( $perPage,  ['*'], static::getPageFieldName(), $page )
				->through( fn( $model ) => $model->toApi() );

			return Pagination::present( $paginator );
		} );
	}
}