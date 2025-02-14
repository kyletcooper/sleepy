<?php

namespace WRD\Sleepy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use WRD\Sleepy\Api\Documentor\Markdown\MarkdownDocumentor;
use WRD\Sleepy\Support\Facades\API;

class GenerateMarkdownCommand extends Command {
	protected $signature = 'sleepy:markdown {out : The output directory for your markdown files.}';

	protected $description = 'Generate markdown documentation for your API, relative to your project root.';

	public function handle(): void
	{
		$base = API::getBase();

		$disk = Storage::build([
			'driver' => 'local',
			'root' => base_path() . $this->argument( 'out' ),
		]);
		
		$documentor = new MarkdownDocumentor();

		$documentor->generate( $base );
		$documentor->toDisk( $disk );
	}
}