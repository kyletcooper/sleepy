<?php

namespace WRD\Sleepy\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as Router;
use Illuminate\Routing\Route;
use WRD\Sleepy\Schema\Layouts\Api\Endpoint as ApiEndpoint;
use WRD\Sleepy\Schema\Layouts\Layout;
use WRD\Sleepy\Support\Facades\API;
use WRD\Sleepy\Support\Tree\Leaf;

class Endpoint extends ApiNode{
	/**
	 * @use Leaf<Route>
	 */
	use Leaf;

	protected array $methods;

	protected mixed $callback;

	protected array $responseCodes = [200, 401, 403];

	protected array $fields = [];

	protected Route $route;

	public function __construct( array|string $methods, ?callable $callback = null ){
		$methods = is_array( $methods ) ? $methods : [ $methods ];

		$this->path = '';
		$this->methods = $methods;

		if( ! is_null($callback) ){
			$this->callback = $callback;
		}
	}

	public function action(callable $callback): static{
		$this->callback = $callback;

		return $this;
	}

	public function responses( int ...$responseCodes ): static{
		$this->responseCodes = $responseCodes;

		return $this;
	}

	public function fields( array $fields ): static{
		$this->fields = array_merge( $this->fields, $fields );

		return $this;
	}

	public function getUrl(array $paramers = []): string {
		if( count( $paramers ) === 0 ){
			return parent::getUrl();
		}

		return route( $this->getName(), $paramers );
	}

	public function getName(): string {
		return $this->getParent()->getName() . '.' . join( "|", $this->methods );
	}

	public function getMethods(): array{
		return $this->methods;
	}

	public function getResponseCodes(): array{
		return $this->responseCodes;
	}

	public function getFields(): array{
		return $this->fields;
	}

	public function getLayout(): Layout {
		return new ApiEndpoint();
	}

	public function matches( Request $request ): bool {
		return $this->route->matches( $request );
	}

	public function make(): void{
		$route = Router::match(
			$this->methods,
			$this->getPath(),
			fn( Request $request, ...$params ) => $this->runAction( $request, ...$params )
		)
			->name( $this->getName() )
			->middleware( $this->getMiddleware() );

		$this->route = $route;
		$this->madeRoutes = true;
	}

	public function runAction( Request $request, ...$params ): mixed{
		$request = API::request();

		$this->checkAuth( $request, ...$params );

		$result = call_user_func( $this->callback, $request, ...$params );

		if( is_a( $result, JsonResponse::class ) ){
			return $result;
		}

		$response = API::response( $result );

		return $response;
	}
}
