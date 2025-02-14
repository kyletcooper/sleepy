<?php

namespace WRD\Sleepy\Support\Tree;

use Closure;

/**
 * @template C of \Node<static, null>
 */
trait Root {
    public array $children = [];
	/**
	 * @return C[]
	 */
	public function getChildren(){
		return $this->children;
	}

	/**
	 * @return static
	 */
	public function clearChildren(): static{
		$this->children = [];

		return $this;
	}

	/**
	 * @param C $child
	 * 
	 * @return static
	 */
	public function addChild( $child ): static{
		$child->setParent( $this );

		$this->children[] = $child;

		return $this;
	}

	/**
	 * @return NodeType
	 */
	public function getNodeType(): NodeType{
		return NodeType::Root;
	}

	/**
	 * @template T
	 * 
	 * @param \Closure(T): bool
	 * 
	 * @return T|null
	 */
	public function findFirst( Closure $predicate ){
		$i = 0;
		$search = $this->getChildren();

		while( true ){
			if( $i >= count( $search ) ){
				return null;
			}

			$target = $search[ $i ];
			$matches = call_user_func( $predicate, $target );

			if( $matches ){
				return $target;
			}

			if( $target->getNodeType() !== NodeType::Leaf ){
				array_push( $search, ...$target->getChildren() );
			}

			$i++;
		}
	}

	/**
	 * @template T
	 * 
	 * @param \Closure(T): bool
	 * 
	 * @return T[]
	 */
	public function findAll( Closure $predicate ){
		$search = $this->getChildren();
		$found = [];

		foreach( $search as $target ){
			$matches = call_user_func( $predicate, $target );

			if( $matches ){
				$found[] = $target;
			}

			if( $target->getNodeType() !== NodeType::Leaf ){
				array_push( $target->getChildren() );
			}
		}

		return $found;
	}
}