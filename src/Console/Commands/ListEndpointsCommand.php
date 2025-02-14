<?php

namespace WRD\Sleepy\Console\Commands;

use Illuminate\Console\Command;
use WRD\Sleepy\Api\Documentor\ListDocumentor;
use WRD\Sleepy\Support\Facades\API;

class ListEndpointsCommand extends Command {
	protected $signature = 'sleepy:list';

	protected $description = 'List all of the endpoints and their fields within your Sleepy API.';

	public function handle(): void
	{
		$base = API::getBase();

		$documentor = new ListDocumentor();

		$documentor->generate( $base );
		$documentor->toConsole( $this );
	}
}