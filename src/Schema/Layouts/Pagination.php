<?php

namespace WRD\Sleepy\Schema\Layouts;

use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use WRD\Sleepy\Schema\Schema;

class Pagination extends Layout {
	public ?Schema $subSchema;

	public function __construct( ?Schema $subSchema = null )
	{
		$this->subSchema = $subSchema ?? Schema::empty();
	}

	public function getSchema(): Schema {
		$linkSchema = (new Link)->getSchema();

		return Schema::object()
			->properties([
				'items' => Schema::array( $this->subSchema ),
				'meta' => Schema::object([
					'page' => Schema::integer()->describe( 'The current page being requested.' ),
					'per_page' => Schema::integer()->describe( 'How many items were requested for the page.' ),
					'first_item' => Schema::integer()->describe( 'The index (1-based) of the first item in this set.' ),
					'last_item' => Schema::integer()->describe( 'The index (1-based) of the last item in this set.' ),
					'total_items' => Schema::integer()->describe( 'The total number of items available across all pages.' ),
					'total_pages' => Schema::integer()->describe( 'The number of the last page.' ),
				]),
				'_links' => Schema::object([
					'first' => $linkSchema,
					'prev' => $linkSchema,
					'self' => $linkSchema,
					'next' => $linkSchema,
					'last' => $linkSchema,
				])
			]);
	}

	public function getMeta( LengthAwarePaginator $paginator ): array{
		return [
			'page' => $paginator->currentPage(),
			'per_page' => $paginator->perPage(),
			'first_item' => $paginator->firstItem(),
			'last_item' => $paginator->lastItem(),
			'total_items' => $paginator->total(),
			'total_pages' => $paginator->lastPage(),
		];
	}

	protected function getPageUrl( LengthAwarePaginator $paginator, int $page ): string{
		$param = $paginator->getPageName();
		return request()->fullUrlWithQuery( [ $param => $page ] );
	}

	public function getLinks( LengthAwarePaginator $paginator ): array{
		$curr = $paginator->currentPage();
		$max = $paginator->lastPage();

		return [
			'first' => $this->getPageUrl( $paginator, 1 ),
			'prev' => $curr > 1 ? $this->getPageUrl( $paginator, $curr - 1 ) : null,
			'self' => $this->getPageUrl( $paginator, $curr ),
			'next' => $curr < $max ? $this->getPageUrl( $paginator, min( $curr + 1, $max ) ) : null,
			'last' => $this->getPageUrl( $paginator, $max ),
		];
	}

	public function getPresenter(): Closure {
		return function( LengthAwarePaginator $paginator ){
			return [
				'items' => $paginator->items(),
				'meta' => $this->getMeta( $paginator ),
				'_links' => $this->getLinks( $paginator )
			];
		};
	}
}