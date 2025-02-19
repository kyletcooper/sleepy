1. Register a layout in the service provider.

```php
public function boot(): void {
	Schema::registerLayout( Link::class, 'Link' );
}
```

2. Use a layout...

```php
Schema::layout( 'Link' )
```

3. Include the layout either in the $defs object or at a separate URL (depending on config).

```json
{
	"$defs": {
		"link": {
			"type": "string"
		}
	},
	"meta": {
		"properties": {
			"next_page": {
				"$ref": "#/$defs/link"
			}
		}
	}
}
```

```json
{
	"meta": {
		"properties": {
			"next_page": {
				"$ref": "https://example.com/api/schema/"
			}
		}
	}
}
```

4. Register schema endpoints.

```php
API::schema('/schema');
```

This registers a route at `/api/schema` that lists all layouts and a route at `/api/schema/{layout}` to get the JSON schema (including `$id`).

5. Automatically register our model schemas when we route them.

```php
API::model( User::class )
```
