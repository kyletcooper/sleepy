<?php

namespace WRD\Sleepy\Api\Documentor;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use WRD\Sleepy\Api\Base;
use WRD\Sleepy\Api\Endpoint;
use WRD\Sleepy\Api\Group;
use WRD\Sleepy\Api\Route;
use WRD\Sleepy\Fields\Field;

class ListDocumentor extends Documentor{
	protected array $out = [];

	protected $verbColors = [
        'ANY' => 'red',
        'GET' => 'blue',
        'HEAD' => '#6C7280',
        'OPTIONS' => '#6C7280',
        'POST' => 'yellow',
        'PUT' => 'yellow',
        'PATCH' => 'yellow',
        'DELETE' => 'red',
    ];

	public function reset(){
		$this->out = [];
		parent::reset();
	}

	public function documentRoot( Base $root ): void{
		// Nothing.
	}

	public function documentGroup( Group $group ): void{
		// Nothing.
	}

	public function documentRoute( Route $route ): void{
		$this->out[] = '';
		$this->out[] = preg_replace( '#({[^}]+})#', '<fg=yellow>$1</>', $route->getPath() );
	}

	public function documentEndpoint( Endpoint $endpoint ): void{
		$method = collect( $endpoint->getMethods() )
		->map( fn ($method) =>
			sprintf( '<fg=%s>%s</>', $this->verbColors[ $method ] ?? 'default', $method ),
		)
		->implode('<fg=#6C7280>|</>');

		$this->out[] = '  ' . $method;
	}

	public function documentField( string $name, Field $field ): void {
		$schema = $field->serialize();

		$this->out[] = '    <fg=gray>"</><fg=blue>' . $name . '</><fg=gray>"</>';

		foreach( $schema as $key => $value ){
			if( ! is_string( $value ) ){
				$value = json_encode( $value );
			}

			$this->out[] = '    <fg=gray>' . $key . ':</> ' . $value;
		}

		$this->out[] = '';
	}

	public function toConsole( Command $command ): void {
		foreach( $this->out as $line ){
			$command->line( $line );
		}
	}

	public function toDisk( Filesystem $filesystem ): void {
		$name = 'sleepy-api-list.txt';
		$filesystem->put( $name, '' );

		foreach( $this->out as $line ){
			$filesystem->append( $name, $line );
		}
	}
}