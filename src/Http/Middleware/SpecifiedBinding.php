<?php

namespace WRD\Sleepy\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use WRD\Sleepy\Http\Exceptions\ApiNotFoundException;

class SpecifiedBinding{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $name, string $class)
    {
        if( $request->route()->hasParameter($name) ){
            $value = $request->route()->parameter($name);
            $inst = (new $class())->resolveRouteBinding($value);
            
            if( ! $inst ){
                abort( new ApiNotFoundException() );
            }

            $request->route()->setParameter($name, $inst);
        }
 
        return $next($request);
    }
}