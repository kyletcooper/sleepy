<?php

namespace WRD\Sleepy\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use WRD\Sleepy\Support\Tree\NodeType;
use Symfony\Component\Routing\Route as SymfonyRoute;
use WRD\Sleepy\Http\Exceptions\ApiUnauthenticatedException;
use WRD\Sleepy\Http\Exceptions\ApiUnauthorizedException;
use WRD\Sleepy\Schema\Layouts\Api\Route as ApiRoute;
use WRD\Sleepy\Schema\Layouts\Link;

abstract class ApiNode{
	protected string $path = "";

	protected string $description = "";
	
	protected array $middleware = [];

	protected mixed $authCallback = null;

	protected bool $madeRoutes = false;

	public function __construct( string $path ){
		$this->path = $path;
	}

	public function describe( string $description ): static{
		$this->description = $description;
		
		return $this;
	}

	public function middleware( array|string $middleware ): static{
		if( ! is_array( $middleware ) ){
			$middleware = [ $middleware ];
		}

		$this->middleware = $middleware;
		
		return $this;
	}

	public function auth( callable $callback ): static{
		$this->authCallback = $callback;
		
		return $this;
	}

	public function getPathAppend(): string{
		return $this->path;
	}

	/**
	 * Get the path to this node.
	 */
	public function getPath(): string{
		if( $this->getNodeType() === NodeType::Root ){
			return $this->getPathAppend();
		}
		else{
			return $this->getParent()->getPath() . $this->getPathAppend();
		}
	}

	public function getPathParameters(): array{
		$route = new SymfonyRoute(
			preg_replace('/\{(\w+?)\?\}/', '{$1}', $this->getPath()), [], [], ['utf8' => true, 'action' => []],
			'', [], []
		);

		return $route->compile()->getVariables();
	}

	/**
	 * Get the description for this node.
	 */
	public function getDescription(): string{
		return $this->description;
	}

	/**
	 * Get the URL to this node.
	 */
	public function getUrl( array $paramers = [] ): string {
		return url( $this->getPath(), $paramers );
	}

	public function getLinkJson(): array{
		$meta = [];

		if( $this->getPathParameters() ){
			$meta['templated'] = true;
		}

		$layout = new Link( $meta );

		return $layout->presentValue( $this->getUrl() );
	}

	/**
	 * Get the route name for this node.
	 */
	public function getName(): string {
		$name = $this->path;

		if( $this->getNodeType() !== NodeType::Root ){
			$name = $this->getParent()->getName() . $this->path;
		}

		$name = str_replace( '/', '.', $name );
		$name = str_replace( '{', '', $name );
		$name = str_replace( '}', '', $name );
		$name = trim( $name, "." );

		return $name;
	}

	public function getNameAppend(): string{
		$name = $this->path;
		
		$name = str_replace( '/', '.', $name );
		$name = str_replace( '{', '', $name );
		$name = str_replace( '}', '', $name );
		$name = trim( $name, "." );

		return $name;
	}

	/**
	 * Get the middleware for this node. Includes parent middlewares.
	 */
	public function getMiddleware(): array{
		if( $this->getNodeType() === NodeType::Root ){
			return $this->middleware;
		}
		else{
			return [ ...$this->getParent()->getMiddleware(), ...$this->middleware ];
		}
	}

	public function getAuthCallbacks(): array {
		$callbacks = [];

		if( ! is_null( $this->authCallback ) ){
			$callbacks[] = $this->authCallback;
		}

		if( $this->getNodeType() === NodeType::Root ){
			return $callbacks;
		}

		return $callbacks;
	}

	public function isPublic(){
		if( $this->getNodeType() === NodeType::Leaf ){
			return count( $this->getAuthCallbacks() ) === 0;
		}
		
		$publicChild = $this->findFirst( fn( ApiNode $node ) => $node->getNodeType() === NodeType::Leaf && count( $node->getAuthCallbacks() ) === 0 );

		return ! is_null( $publicChild );
	}

	public function checkAuth( Request $request, ...$params ): void {
		$callbacks = $this->getAuthCallbacks();

		if( count( $callbacks ) === 0 ){
			return; // No auth.
		}

		if( ! Auth::check() ){
			abort( new ApiUnauthenticatedException() );
		}

		foreach( $callbacks as $callback ){
			$authorized = call_user_func( $callback, $request, ...$params );

			if( $authorized !== true ){
				abort( new ApiUnauthorizedException() );
			}
		}
	}

	/**
	 * Makes the API routes.
	 */
	public function make(): void{
		if( $this->getNodeType() !== NodeType::Leaf ){
			foreach( $this->getChildren() as $child ){
				$child->make();
			}
		}

		$this->madeRoutes = true;
	}

	protected function makeEndpointsDescription(): void{
		Route::options( $this->getPath(), function(){
			if( ! Auth::check() && ! $this->isPublic() ){
				abort( new ApiUnauthenticatedException() );
			}
			
			return ApiRoute::fake( $this );
		})
			->middleware( $this->getMiddleware() )
			->name( $this->getName() . '.OPTIONS' );
	}

	protected function makeChildrenOverview( string $layoutClass ): void{
		Route::get( $this->getPath(), function() use ( $layoutClass ) {
			if( ! Auth::check() && ! $this->isPublic() ){
				abort( new ApiUnauthenticatedException() );
			}

			return $layoutClass::present( $this );
		})
			->name( $this->getName() );
	}
}