<?php

namespace WRD\Sleepy\Api\Documentor\Markdown;

use WRD\Sleepy\Support\Tree\Leaf;

class File {
	use Leaf;

	public string $name;

	public string $contents;

	public function __construct( string $name, string $contents = '' )
	{
		$this->name = $name;
		$this->contents = $contents;
	}

	public function append( string $line ){
		$this->contents .= PHP_EOL . $line;
	}
}