<?php

namespace WRD\Sleepy\Fields;

use WRD\Sleepy\Fields\HasApiModel;
use WRD\Sleepy\Fields\Attributes\HasAttributes;
use WRD\Sleepy\Fields\Embeds\HasEmbeds;
use WRD\Sleepy\Fields\Filters\HasFilters;
use WRD\Sleepy\Fields\Links\HasLinks;
use WRD\Sleepy\Fields\Pagination\HasPagination;
use WRD\Sleepy\Fields\Sorts\HasSorts;

trait HasApi{
	use HasApiModel, HasAttributes, HasFilters, HasSorts, HasEmbeds, HasLinks, HasPagination;
}