<?php

namespace WRD\Sleepy\Api\Documentor\Markdown;

use WRD\Sleepy\Support\Tree\Node;

class Directory {
	use Node;

	public string $name;

	public function __construct( string $name, array $children = [] )
	{
		$this->name = $name;
		$this->children = $children;
	}
}