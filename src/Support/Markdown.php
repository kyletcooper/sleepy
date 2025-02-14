<?php

namespace WRD\Sleepy\Support;

use Stringable;

class Markdown implements Stringable{
	protected string $value = "";

	public function text( string $text ): static {
		$this->value .= $text;
		return $this;
	}

	public function break(): static{
		$this->value .= PHP_EOL;

		return $this;
	}

	public function line( string $text ): static{
		$this->text( $text );
		$this->break();

		return $this;
	}

	public function heading( string $text, int $depth = 2 ): static{
		$this->line( str_repeat( "#", $depth ) . ' ' . $text );

		return $this;
	}

	public function bold( string $text ): static{
		$this->text( "**$text**" );

		return $this;
	}

	public function italic( string $text ): static{
		$this->text( "*$text*" );

		return $this;
	}

	public function link( string $url, string $label = null ): static{
		if( is_null( $label ) ){
			$this->text( "<$url>" );
		}

		$this->text( "[$label]($url)" );

		return $this;
	}

	public function blockquote( string $text ): static{
		$this->line( '> ' . $text );

		return $this;
	}

	public function table( array $rows ): static{
		// $md->table(['name' => 'Egan', 'type' => 'string'], ['name' => 'Egan', 'type' => 'string']);

		$values = collect( $rows );

		$columns = $values
			->map( fn( array $row ) => array_keys( $row ) )
			->flatten()
			->unique()
			->values();
		
		$widths = [];

		$this->value .= '|';
		$break = '|';

		foreach( $columns as $col ){
			$col = str_replace( '|', '\|', $col );
			$widths[ $col ] = strlen( $col );

			foreach( $rows as $row ){
				$value = $row[ $col ] ?? "";
				$value = str_replace( '|', '\|', $value );
				$widths[ $col ] = max( strlen( $value ), $widths[ $col ] );
			}

			$this->value .= ' ' . str_pad( $col, $widths[ $col ] ) . ' |';
			$break .= str_repeat( '-', $widths[ $col ] + 2 ) . '|';
		}

		$this->value .= PHP_EOL;
		$this->value .= $break;
		$this->value .= PHP_EOL;

		foreach( $rows as $row ){
			$this->value .= '|';

			foreach( $columns as $col ){
				$value = $row[ $col ] ?? "";
				$width = $widths[ $col ];

				$value = str_replace( '|', '\|', $value );

				$this->value .= ' ' . str_pad( $value, $width, ' ' ) . ' |';
			}

			$this->value .= PHP_EOL;
		}

		return $this;
	}

	public function __toString(){
		return $this->value;
	}
}