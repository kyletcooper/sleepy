<?php

namespace WRD\Sleepy\Layouts;

use Carbon\Carbon;
use DateTimeInterface;
use WRD\Sleepy\Schema\Schema;

class Datetime extends Layout{
    public function schema(): Schema {
        return Schema::string( 'date-time' );
    }

    /**
     * @param DateTimeInterface $value
     */
	public function present( $value ): string {
        return Carbon::instance( $value )->toIso8601String();
    }
}
