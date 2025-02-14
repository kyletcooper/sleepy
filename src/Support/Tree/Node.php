<?php

namespace WRD\Sleepy\Support\Tree;

/**
 * @template P of \Node<null, static>
 * @template C of \Node<static, null>|\Leaf<static>
 */
trait Node {
	/**
	 * @use \Root<C>
	 */
	use Root {
		Root::getNodeType as private rootgetNodeType;
	}

	/**
	 * @use \Leaf<P>
	 */
	use Leaf {
		Leaf::getNodeType as private leafgetNodeType;
	}

	/**
	 * @return NodeType
	 */
	public function getNodeType(): NodeType{
		return NodeType::Node;
	}
}