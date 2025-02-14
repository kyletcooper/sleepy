<?php

namespace WRD\Sleepy\Schema;

use Carbon\Carbon;
use Closure;
use DateInterval;
use Exception;
use WRD\Sleepy\Schema\Exceptions\UnsupportedSchemaException;

class Formats{
	private static array $formats = [];

	private static array $coreFormats = [];

	private static bool $registredCoreFormats = false;

	/**
	 * Matchers can either return a false value of throw an exception to indicate the value does not match.
	 * 
	 * Any value that is not strictly false (such as null, 0, '', etc.) is considered a match.
	 */
	static public function registerFormat( string $name, Closure $matcher ): void{
		static::$formats[ $name ] = $matcher;
	}

	static public function deregisterFormat( string $name ): void{
		unset( static::$formats[ $name ] );
	}

	static private function registerCoreFormat( string $name, Closure $matcher ): void{
		static::$coreFormats[ $name ] = $matcher;
	}

	static public function getFormat( string $name ): Closure{
		static::registerCoreFormats();

		$fallback = fn() => throw new UnsupportedSchemaException( "This format is not supported." );

		return static::$formats[ $name ] ?? static::$coreFormats[ $name ] ?? $fallback;
	}

	static public function matches( string $format, mixed $value ): bool{
		try{
			$matcher = static::getFormat( $format );
			$matches = call_user_func( $matcher, $value );

			return $matches !== false;
		}
		catch( Exception $e ){
			return false;
		}
	}

	static private function registerCoreFormats(){
		if( static::$registredCoreFormats ){
			return;
		}

		static::$registredCoreFormats = true;

		// The date creators throw on error.
		static::registerCoreFormat( 'date-time', fn( $value ) => Carbon::createFromFormat( 'c', $value ) );
		static::registerCoreFormat( 'date', fn( $value ) => Carbon::createFromFormat( 'Y-m-d', $value ) );
		static::registerCoreFormat( 'time', fn( $value ) => Carbon::createFromFormat( 'H:i:sp', $value ) );
		static::registerCoreFormat( 'duration', fn( $value ) => new DateInterval( $value ) );

		static::registerCoreFormat( 'uri', fn( $value ) => filter_var( $value, FILTER_VALIDATE_URL ) );
		static::registerCoreFormat( 'url', fn( $value ) => filter_var( $value, FILTER_VALIDATE_URL ) );

		static::registerCoreFormat( 'ipv4', fn( $value ) => filter_var( $value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) );
		static::registerCoreFormat( 'ipv6', fn( $value ) => filter_var( $value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) );

		static::registerCoreFormat( 'email', fn( $value ) => filter_var( $value, FILTER_VALIDATE_EMAIL ) );
 		
		/**
		 * # Note
		 * Not implemented:
		 * - "idn-email"
		 * - "hostname"
		 * - "idn-hostname"
		 * - "uuid"
		 * - "uri-reference"
		 * - "iri"
		 * - "iri-reference"
		 * - "uri-template"
		 * - "json-pointer"
		 * - "relative-json-pointer"
		 * - "regex",
		 */
	}
}