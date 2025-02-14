<?php

namespace WRD\Sleepy\Api\Documentor\Markdown;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Response;
use WRD\Sleepy\Api\Base;
use WRD\Sleepy\Api\Documentor\Documentor;
use WRD\Sleepy\Api\Endpoint;
use WRD\Sleepy\Api\Group;
use WRD\Sleepy\Api\Route;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Support\Tree\Transformer;
use WRD\Sleepy\Support\Tree\Walker;
use WRD\Sleepy\Support\Markdown;

class MarkdownDocumentor extends Documentor{
	protected ?Directory $root = null;

	public function reset(){
		$this->root = null;

		parent::reset();
	}

	public function documentRoot( Base $root ): void{
		$transformer = new Transformer();

		$transformer->rule( Base::class, fn( Base $root ) => new Directory( $root->getNameAppend() ) );
		$transformer->rule( Group::class, fn( Group $group ) => new Directory( $group->getNameAppend() ) );
		$transformer->rule( Route::class, fn( Route $route ) => new File( $route->getNameAppend(), $this->generateRouteMarkdown( $route ) ) );

		$this->root = $transformer->transform( $root );
	}

	protected function generateRouteMarkdown( Route $route ): string{
		$md = new Markdown();

		$schema = $route->getSchema();

		if( $schema ){
			$md->heading( 'Schema', 1 );
			$md->break();
			$md->table( collect( $schema->properties )
				->map( fn( $prop, $name ) => [
					'Field' => $name,
					'Type' => join( "|", $prop->types ),
					'Description' => $prop->description,
				] )
				->all()
			);
		}

		$endpoints = $route->getChildren();

		if( $endpoints ){
			$md->break();
			$md->heading( 'Endpoints', 1 );

			foreach( $endpoints as $endpoint ){
				$md->break();
				$md->text( $this->generateEndpointMarkdown( $endpoint ) );
			}
		}

		return $md;
	}

	protected function generateEndpointMarkdown( Endpoint $endpoint ): string{
		$md = new Markdown();

		$methods = join( ", ", $endpoint->getMethods() );
		$md->heading( "[$methods] " . $endpoint->getPath() );
		$md->break();
		$md->heading( "Fields", 3 );

		foreach( $endpoint->getFields() as $name => $field ){
			$md->text( $this->generateFieldMarkdown( $name, $field ) );
		}

		$md->break();
		$md->heading( "Response Codes", 3 );

		$md->table( collect( $endpoint->getResponseCodes() )
			->map( fn( $code ) => ['Code' => $code, 'Message' => Response::$statusTexts[ $code ]] )
			->all()
		);

		$md->break();

		return $md;
	}

	protected function generateFieldMarkdown( string $name, Field $field ): string{
		$md = new Markdown();

		$md->bold( $name )->break();

		if( $field->types ){
			$md->italic( join( "|", $field->types ) )->break();
		}
		
		if( $field->description ){
			$md->line( $field->description );
		}

		if( $field->examples ){
			$md->blockquote( 'For example: ' . join( ", ", $field->examples ) );
		}

		if( $field->deprecated ){
			$md->blockquote( ':warning: Deprecated' );
		}

		$md->break();

		return $md;
	}

	public function toConsole( Command $command ): void {
		$walker = new Walker();

		$walker->on( Directory::class, fn( Directory $dir, Walker $walker ) => $command->line( str_repeat( '  ', $walker->getDepth() ) . '<fg=gray>⮡ </>' . $dir->name ) );
		$walker->on( File::class, fn( File $file, Walker $walker ) => $command->line( str_repeat( '  ', $walker->getDepth() ) . '<fg=gray>⮡ </><fg=blue>' . $file->name . '</><fg=gray>.md</>' ) );

		$walker->walk( $this->root );
	}

	public function toDisk( Filesystem $filesystem ): void {
		$walker = new Walker();

		$walker->on( Directory::class, function( Directory $dir, Walker $walker ) use ( $filesystem ){
			$pathTo = $walker->getStack()->values()->pluck( 'name' )->reverse()->join("/");
			$path = $pathTo . '/' . $dir->name;

			$filesystem->makeDirectory( $path );
		});

		$walker->on( File::class, function( File $file, Walker $walker ) use ( $filesystem ){
			$pathTo = $walker->getStack()->values()->pluck( 'name' )->reverse()->join("/");
			$path = $pathTo . '/' . $file->name . '.md';

			$filesystem->put( $path, $file->contents );
		});

		$walker->walk( $this->root );
	}
}