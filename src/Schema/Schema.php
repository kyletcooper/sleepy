<?php

namespace WRD\Sleepy\Schema;

use JsonSerializable;
use ReflectionClass;

/**
 * Intentional diversions:
 * 	- Use of required keyword on individual property schemas rather than the object.
 */
class Schema implements JsonSerializable{
	public const STRING = "string";
	public const NULL = "null";
	public const NUMBER = "number";
	public const INTEGER = "integer";
	public const BOOLEAN = "boolean";
	public const ARRAY = "array";
	public const OBJECT = "object";

	public const MERGE_COMBINE = "MERGE_COMBINE";
	public const MERGE_ONE_OF = "MERGE_ONE_OF";

	public array $types = [];

	public ?string $title = null;

	public ?string $description = null;

	public ?array $examples = null;

	public ?bool $deprecated = null;

	public ?array $enum = null;

	public mixed $const = null;

	public ?string $format = null;

	public ?string $pattern = null;

	public ?int $minLength = null;

	public ?int $maxLength = null;

	public ?Schema $items = null;

	public ?int $minItems = null;

	public ?int $maxItems = null;

	public ?bool $uniqueItems = null;

	public ?array $properties = null;

	public false|Schema|null $additionalProperties = null;

	public ?int $minProperties = null;

	public ?int $maxProperties = null;

	public ?bool $required = null;

	public mixed $default = null;

	public ?bool $readOnly = null;

	public ?bool $writeOnly = null;

	public int|float|null $min = null;

	public int|float|null $max = null;

	public ?bool $exclusiveMinimum = null;

	public ?bool $exclusiveMaximum = null;

	public int|float|null $multipleOf = null;

	public ?array $allOf = null;

	public ?array $anyOf = null;

	public ?array $oneOf = null;

	public array|string|null $custom = null;

	public function __construct(array|string $types = "")
	{
		$this->type( $types );
	}

	static public function create(array|string $types = ""): static{
		return new static($types);
	}

	static public function string(?string $format = null): static{
		return (new static(static::STRING))->format($format);
	}

	static public function null(): static{
		return new static(static::NULL);
	}

	static public function number(): static{
		return new static(static::NUMBER);
	}

	static public function integer(): static{
		return new static(static::INTEGER);
	}

	static public function boolean(): static{
		return new static(static::BOOLEAN);
	}

	static public function array(?Schema $items = null): static{
		return (new static(static::ARRAY))->items($items);
	}

	/**
	 * @param array<string, Schema> $properties
	 */
	static public function object(?array $properties = null): static{
		return (new static(static::OBJECT))->properties($properties);
	}

	static public function empty(): static{
		return ( new static( [] ) );
	}

	static public function enumeration( array $values ): static{
		return ( new static( [] ) )->enum( $values );
	}

	static public function constant( array $values ): static{
		return ( new static( [] ) )->const( $values );
	}

	public function type(array|string $types = ""): static{
		$this->types = is_array( $types ) ? $types : [$types];

		return $this;
	}

	public function nullable(): static{
		if( ! $this->hasType( static::NULL ) ){
			$this->types = [ ...$this->types, static::NULL ];
		}

		return $this;
	}

	public function title(?string $title): static{
		$this->title = $title;

		return $this;
	}

	public function describe(?string $description): static{
		$this->description = $description;

		return $this;
	}

	public function examples(array $examples): static{
		$this->examples = $examples;

		return $this;
	}

	public function deprecated(): static{
		$this->deprecated = true;

		return $this;
	}

	public function enum(?array $enum): static{
		$this->enum = $enum;

		return $this;
	}

	public function const(mixed $const): static{
		$this->const = $const;

		return $this;
	}
	
	public function required(): static{
		$this->required = true;

		return $this;
	}

	public function optional(): static{
		$this->required = false;

		return $this;
	}

	public function default(mixed $value): static{
		$this->default = $value;

		return $this;
	}

	public function readonly(): static{
		$this->readOnly = true;

		return $this;
	}

	public function writeonly(): static{
		$this->writeOnly = true;

		return $this;
	}

	public function format(?string $format): static{
		$this->format = $format;

		return $this;
	}

	public function pattern(?string $pattern): static{
		$this->pattern = $pattern;

		return $this;
	}

	public function minLength(?string $minLength): static{
		$this->minLength = $minLength;

		return $this;
	}

	public function maxLength(?string $maxLength): static{
		$this->maxLength = $maxLength;

		return $this;
	}

	public function min( int|float|null $min, ?bool $exclusive = null ): static{
		$this->min = $min;

		if( ! is_null( $exclusive ) ){
			$this->exclusiveMinimum = $exclusive;
		}

		return $this;
	}

	public function max( int|float|null $max, ?bool $exclusive = null ): static{
		$this->max = $max;

		if( ! is_null( $exclusive ) ){
			$this->exclusiveMaximum = $exclusive;
		}

		return $this;
	}

	public function multipleOf(int|float|null $multipleOf): static{
		$this->multipleOf = $multipleOf;

		return $this;
	}

	public function items(?Schema $items): static{
		$this->items = $items;

		return $this;
	}

	public function minItems(int $minItems): static{
		$this->minItems = $minItems;

		return $this;
	}

	public function maxItems(int $maxItems): static{
		$this->maxItems = $maxItems;

		return $this;
	}

	public function uniqueItems(): static{
		$this->uniqueItems = true;

		return $this;
	}
	
	public function properties(?array $properties): static{
		$this->properties = $properties;

		return $this;
	}

	public function additionalProperties(false|Schema|null $additionalProperties): static{
		$this->additionalProperties = $additionalProperties;

		return $this;
	}

	public function minProperties(int $minProperties): static{
		$this->minProperties = $minProperties;

		return $this;
	}

	public function maxProperties(int $maxProperties): static{
		$this->maxProperties = $maxProperties;

		return $this;
	}

	public function allOf(array $schemas): static{
		$this->allOf = $schemas;

		return $this;
	}

	public function anyOf(array $schemas): static{
		$this->anyOf = $schemas;

		return $this;
	}

	public function oneOf(array $schemas): static{
		$this->oneOf = $schemas;

		return $this;
	}

	public function custom(array|string $rules): static{
		$this->custom = $rules;

		return $this;
	}


	/** Utils */

	public function isType( string $type ){
		return count( $this->types ) === 1 && $this->types[0] === $type;
	}

	public function hasType( string $type ){
		return in_array( $type, $this->types );
	}

	public function hasComposition(){
		return ! is_null( $this->allOf ) || ! is_null( $this->anyOf ) || ! is_null( $this->oneOf );
	}


	/** Merging & Cloning */

	private function getSchemaProperties(): array{
		$reflect = new ReflectionClass( self::class );
		$properties = $reflect->getProperties();
		$values = [];

		foreach( $properties as $prop ) {
			if( $prop->class === self::class && $prop->isPublic() && ! $prop->isStatic() ) {
				$values[ $prop->getName() ] = $prop->getValue( $this );
			}
		}

		return $values;
	}

	public function exportSchema( string $class = Schema::class ): Schema {
		$clone = new $class();
		
		foreach ( $this->getSchemaProperties() as $key => $value) {
            $clone->$key = $value;
        }

		return $clone;
	}

	public function importSchema( Schema $from ): static {
		foreach ( $from->getSchemaProperties() as $key => $value) {
            $this->$key = $value;
        }

		return $this;
	}

	public function mergeIn( Schema $schema, ?string $behaviour = null ): void{
		$merger = new Merger( $behaviour );

		$merged = $merger->merge( $this, $schema );

		$this->importSchema( $merged );
	}

	
	/** Coercion & Validation */

	/**
	 * @throws \WRD\Sleepy\Schema\Exceptions\CouldNotCoerceException
	 */
	public function coerce( mixed $value ): mixed {
		$coercer = new Coercer( $this );

		return $coercer->coerce( $value );
	}

	/**
	 * @throws \WRD\Sleepy\Schema\Exceptions\SchemaException
	 */
	public function validate( mixed $value ): void {
		$validator = new Validator( $this );

		$validator->validate( $value );
	}

	
	/** Serialization */

	public function serialize(): object{
		$serializer = new Serializer();

		return $serializer->serialize( $this );
	}

	public function jsonSerialize(): object{
		return $this->serialize();
	}
}
