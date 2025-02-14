<?php

namespace WRD\Sleepy\Api\Documentor;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use WRD\Sleepy\Api\Base;
use WRD\Sleepy\Api\Endpoint;
use WRD\Sleepy\Api\Group;
use WRD\Sleepy\Api\Route;
use WRD\Sleepy\Fields\Field;
use WRD\Sleepy\Support\Stack;
use WRD\Sleepy\Support\Tree\Walker;

abstract class Documentor{
	protected Walker $walker;

	abstract public function toConsole( Command $command ): void;

	abstract public function toDisk( Filesystem $filesystem ): void;

	public function documentRoot( Base $root ): void {}

	public function documentGroup( Group $group ): void {}

	public function documentRoute( Route $route ): void {}

	public function documentEndpoint( Endpoint $endpoint ): void {}

	public function documentField( string $name, Field $field ): void {}

	public function reset(){
		$this->walker = new Walker();

		$this->walker->on( Base::class, [ $this, 'documentRoot' ] );
		$this->walker->on( Group::class, [ $this, 'documentGroup' ] );
		$this->walker->on( Route::class, [ $this, 'documentRoute' ] );
		$this->walker->on( Endpoint::class, function( Endpoint $endpoint ) {
			$this->documentEndpoint( $endpoint );

			foreach( $endpoint->getFields() as $name => $field ){
				$this->documentField( $name, $field );
			}
		});
	}
	
	public function generate( Base $root ){
		$this->reset();

		$this->walker->walk( $root );
	}
}