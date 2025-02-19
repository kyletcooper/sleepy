<?php

namespace WRD\Sleepy\Fields\Links;

use WRD\Sleepy\Fields\Concerns\Output;
use WRD\Sleepy\Layouts\Link as LayoutsLink;
use WRD\Sleepy\Schema\Schema;

class Link extends Schema{
	use Output;

	public function __construct()
	{
		parent::__construct();

		$this->layout( new LayoutsLink() );
	}
}