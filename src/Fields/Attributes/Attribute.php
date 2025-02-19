<?php

namespace WRD\Sleepy\Fields\Attributes;

use WRD\Sleepy\Fields\Concerns\Output;
use WRD\Sleepy\Fields\Concerns\Write;
use WRD\Sleepy\Fields\Field;

class Attribute extends Field{
	use Output, Write;
}