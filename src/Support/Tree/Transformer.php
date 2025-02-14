<?php

namespace WRD\Sleepy\Support\Tree;

use Closure;

class Transformer {
	protected array $rules = [];
	
	public function rule( string $from, Closure $to ): static{
		$this->rules[ $from ] = $to;

		return $this;
	}

	protected function transformLeaf( object $leaf ): object {
		$class = $leaf::class;
		$transformed = clone $leaf;
		
		if( array_key_exists( $class, $this->rules ) ){
			$transformed = call_user_func( $this->rules[ $class ], $transformed );
		}

		return $transformed;
	}

	protected function transformRoot( object $root ): object {
		$class = $root::class;
		$children = $root->getChildren();
		$transformed = clone $root;
		
		if( array_key_exists( $class, $this->rules ) ){
			$transformed = call_user_func( $this->rules[ $class ], $transformed );
		}

		if( $transformed->getNodeType() === NodeType::Leaf ){
			return $transformed;
		}
		
		$transformed->clearChildren();

		foreach( $children as $child ){
			if( $transformed->getNodeType() === NodeType::Leaf ){
				$transformed->addChild( $this->transformLeaf( $child ) );
			}
			else{
				$transformed->addChild( $this->transformRoot( $child ) );
			}
		}

		return $transformed;
	}

	public function transform( object $root ): object{
		return $this->transformRoot( $root );
	}
}