<?php

namespace WRD\Sleepy\Support\Tree;

/**
 * @template P of \Node<null, static>
 */
trait Leaf {
	public $parent = null;

	/**
	 * @return P
	 */
	public function getParent(){
		return $this->parent;
	}

	/**
	 * @param P $parent
	 * 
	 * @return static
	 */
	public function setParent( $parent ): static{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * @return NodeType
	 */
	public function getNodeType(): NodeType{
		return NodeType::Leaf;
	}
}