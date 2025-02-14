<?php

namespace WRD\Sleepy\Support\Tree;

use WRD\Sleepy\Support\Stack;

class Walker {
	protected Stack $stack;

	protected array $hooks = [];
	
	public function on( string $class, callable $callback ): static{
		$this->hooks[ $class ] = $callback;

		return $this;
	}

	protected function callOn( object $obj ): void{
		if( array_key_exists( $obj::class, $this->hooks ) ){
			call_user_func( $this->hooks[ $obj::class ], $obj, $this );
		}
	}

	public function getDepth(){
		return $this->stack->count();
	}

	public function getStack(): Stack{
		return $this->stack;
	}

	public function walk( object $root ): void{
		$this->stack = new Stack();

		$this->walkRoot( $root );
	}

	protected function walkRoot( object $root ): void {
		$this->callOn( $root );
		
		$this->stack->push( $root );

		foreach( $root->getChildren() as $child ){
			if( $child->getNodeType() === NodeType::Leaf ){
				$this->walkLeaf( $child );
			}
			else{
				$this->walkRoot( $child );
			}
		}

		$this->stack->pop();
	}

	protected function walkLeaf( object $leaf ): void {
		$this->callOn( $leaf );
	}
}