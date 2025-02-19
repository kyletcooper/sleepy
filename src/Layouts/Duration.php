<?php

namespace WRD\Sleepy\Layouts;

use Carbon\CarbonInterval;
use DateInterval;
use WRD\Sleepy\Schema\Schema;

class Duration extends Layout{
    public function schema(): Schema {
        return Schema::string( 'duration' );
    }

    /**
     * @param DateInterval $value
     */
	public function present( $value ): string {
        return CarbonInterval::getDateIntervalSpec( $value );
    }
}
