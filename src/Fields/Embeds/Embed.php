<?php

namespace WRD\Sleepy\Fields\Embeds;

use WRD\Sleepy\Fields\Concerns\Output;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Schema\Layouts\Embed as LayoutsEmbed;

class Embed extends Field{
	use Output;

	public string $model;

	public function __construct()
	{
		parent::__construct(...func_get_args());
	}

	public function model( string $model, bool $mergedWithLink = false ){
		$this->model = $model;

		$layout = new LayoutsEmbed( $model, $mergedWithLink );

		$this->layout( $layout );

		return $this;
	}
}