<?php

namespace WRD\Sleepy\Schema;

class Merger{
	private string $behaviour;

	private ?Schema $a;

	private ?Schema $b;

	private ?Schema $merged;

	public const APPEND = "APPEND";
	public const OVERWRITE = "OVERWRITE";
	public const ONE_OF = "ONE_OF";
	public const ANY_OF = "ANY_OF";
	public const ALL_OF = "ALL_OF";

	public function __construct( ?string $behaviour = null )
	{
		if( is_null( $behaviour ) ){
			$behaviour = static::APPEND;
		}

		$this->behaviour = $behaviour;
	}

	public function merge( Schema $a, Schema $b ): Schema{
		$this->a = Schema::empty()->importSchema( $a );
		$this->b = Schema::empty()->importSchema( $b );

		$this->merged = Schema::empty();

		if( $this->isComposable() ){
			$this->compose();
		}
		else if( $this->behaviour === static::OVERWRITE ){
			$this->overwrite();
		}
		else{ 
			$this->append();
		}

		$merged = $this->merged;

		$this->a = null;
		$this->b = null;
		$this->merged = null;

		return $merged;
	}

	private function isComposable(): bool{
		$composable = [static::ONE_OF, static::ANY_OF, static::ALL_OF];

		return in_array( $this->behaviour, $composable, true );
	}

	private function compose(): void{
		switch( $this->behaviour ){
			case static::ONE_OF:
				$this->merged->oneOf( [ $this->a, $this->b ] );
				break;
			
			case static::ANY_OF:
				$this->merged->anyOf( [ $this->a, $this->b ] );
				break;

			case static::ALL_OF:
				$this->merged->allOf( [ $this->a, $this->b ] );
				break;
		}
	}

	private function overwrite(): void{
		$this->merged->importSchema( $this->a )->importSchema( $this->b );
	}

	private function append(){
		$this->overwrite();

		$this->appendTypes();
		$this->appendProperties();
		$this->appendItems();
	}

	private function appendTypes(){
		$types = [ ...$this->a->types, ...$this->b->types ];
		$types = array_unique( $types );

		$this->merged->type( $types );
	}

	private function appendProperties(): void{
		$aProps = $this->a->properties ?? [];
		$bProps = $this->b->properties ?? [];
		$properties = array_merge( $aProps, $bProps );

		$this->merged->properties( $properties );
	}

	private function appendItems(): void{
		if( is_null( $this->a->items ) || is_null( $this->b->items ) ){
			return;
		}

		$this->merged->items( Schema::empty()->anyOf( [ $this->a->items, $this->b->items ] ) );
	}
}