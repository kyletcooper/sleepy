# Sleepy

A schema-first & restful API toolkit for Laravel.

> :warning: This package is under active development.

**Contents**<br />
[Installation](#installation)<br />
[Setup](#setup)<br />
[Core Concepts](#core-concepts)<br />
[Schema](#schema)<br />
[Attributes](#attributes)<br />
[Embedding](#embedding)<br />
[Filtering](#filtering)<br />
[Sorting](#sorting)<br />
[Pagination](#pagination)<br />
[Authentication & Authorization](#authentication--authorization)<br />
[Discovery](#discovery)<br />
[Exception Handling](#exception-handling)<br />
[Commands](#commands)<br />
[Configuration](#configuration)<br />

# Installation

You can install Sleepy using Composer:

```
composer install wrd/sleepy
```

# Setup

To get your Sleepy API started, create a new route file to include all of your API routes.

```php
// route/api.php

use WRD\Sleepy\Support\Facades\API;

API::base('/api', function() {

  API::group( '/v1', function(){
    // This is optional, see authentication below.
    API::login();

    // Setup the collection & self routes for your model.
    API::model( Post::class );

  });
});
```

Then you can set up your Model to be API compatible using the `HasApiAll` trait.

The `attributes`, `filters` and `sorts` methods are all optional, Sleepy will provide sensible defaults.

> `HasApi` is a shorthand to include `HasApiModel`, `HasAttributes`, `HasFilters`, `HasSorts`, `HasEmbeds`, `HasLinks` and `HasPagination` all at once. You can choose to provide these individually if you'd prefer more control.

```php
// App/Models/Post.php

use WRD\Sleepy\Fields\HasApi;
use WRD\Sleepy\Fields\Filters\Filters;
use WRD\Sleepy\Fields\Sorts\Sorts;
use WRD\Sleepy\Fields\Attributes\Attr;
//...

class Post extends Model
{
  use HasApi;

	// This defines the schema of your model.
	public static function attributes(): array {
    return [
		  // This will automatically determine the model's key.
      'id' => Attr::key(),

      // This will be the class basename of the model.
      'type' => Attr::basename(),

			// This will pull from/write to the 'title' property from the model.
      'title' => Attr::string()->required(),

			// This will pull from/write to the 'body' property from the model.
			'body' => Attr::string()->required(),

			// You can prevent users from updating a field using ->readonly()
			// Here, 'url' is the format of the string.
      'thumbnail' => Attr::string( 'url' )->readonly(),
    ];
  }

	// Controls the ways consumers can filter your model's collection endpoint.
  public static function filters(): array{
    return [
      // Search allows you to do a fuzzy search across multiple columns.
      // Here, 'search' is the name of the query parameter. The names of the columns are passed to the filter.
      'search' => Filters::search('title,body'),
    ];
  }

	// Controls the ways consumers can sort your model's collection endpoint.
  public static function sorts(): array{
    return [
      // Allow sorting by the title alphabetically.
      'title' => Sorts::alphabetical( "title" ),

			// Here, 'title' is the value that must be passed to the 'order_by' query parameter. The name of the database column is passed to the sort.
			'published' => Sorts::date( "created_at" ),
    ];
  }
}
```

And you're done! You can now access your routes at https://example.com/api/v1/post and https://example.com/api/v1/post/:key. You can now search and sort your collection, create new posts and delete or update existing ones.

> :warning: Your model routes will automatically have authorization guards in place, acting on the assumption that you are using the Laravel policy system, so you'll need to be authenticated to see these endpoints.

## Custom Routes

You're not required to attach all of your routes and endpoints to models, of course. You can create a custom route like so:

```php
// routes/api.php

API::base('/api', function() {

  API::group( '/v1', function(){

    API::route( '/custom', function(){

      API::endpoint( 'GET' )
				->fields([
					'your_name' => Field::string()->default('world')
				])
				->describe('Says hello to you.')
				->action(function( ApiRequest $request ){
					return 'Hello ' . $request->fields()->get('world')
				})
				->responses( 200 )

		});

  });

});
```

# Core Concepts

Sleepy helps you quickly create restful APIs for your models using some simple shorthands, handling the routing, filtering, pagination and attribute reading/writing for you.

You can create routes independently of your models, but Sleepy shines best via it's `HasApi` trait, which will instantly prepare your model to have endpoints handling the `viewAll`, `create`, `show`, `update` and `delete` actions.

## Schema-First

In Sleepy, almost everything you interact with is a sub-class of Schema - based on JSON Schema (with some differences, noted below). This allows you to document as you go and keeps your API consistent.

## API Structure

Sleepy APIs form a tree, starting with a Base (declared using `API::base()`)..

Each Base has one or more Groups. There can only be one API Base per application.

Each Group has one or more sub-groups or Routes. You can use groups to create a path that routes nested under (such as for versioning).

Each Route has one or more Endpoints.

Each Endpoint has a method (`GET`, `POST`, etc.) and a callback action.

You can add middleware to any node in the API tree by using the `middelware` method.

# Schema

Everything in Sleepy is driven by Schema. Our `Attribute`, `Filter`, `Sort` and generic `Field` classes all extend this schema base class. This allows us to build a flexible and consistent validation model and document as we go.

The Schema class uses a fluent approach to build up your schema. Below is documentation for each of the methods available.

## Validation Methods

| Method     | Parameter     | Description                              |
| ---------- | ------------- | ---------------------------------------- |
| `type`     | string\|array | Set the list of allowed types.           |
| `nullable` | -             | Add `null` to the list of allowed types. |
| `enum`     | array<mixed>  | Ensure the value is one of the provided. |
| `const`    | mixed         | The value must be exactly the provided.  |
| `required` | -             | The value must be provided.              |
| `optional` | -             | Sets required to false.                  |

### Strings

| Method      | Parameter | Description                                                                                                                             |
| ----------- | --------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| `default`   | mixed     | Provide a value for the request to fallback to if none is provided.                                                                     |
| `format`    | string    | Provide the key of a format, registered to the WRD\Sleepy\Schema\Format class. See [formats](#string-formats) below for allowed values. |
| `pattern`   | string    | A regex pattern to match against.                                                                                                       |
| `minLength` | int       | The minimum length of the value.                                                                                                        |
| `maxLength` | int       | The maximum length of the value.                                                                                                        |

### Numbers / Integers

| Method       | Parameter  | Description                                                    |
| ------------ | ---------- | -------------------------------------------------------------- |
| `min`        | int\|float | The minimum numeric value.                                     |
| `max`        | int\|float | The maximum numeric value.                                     |
| `multipleOf` | int\|float | Indicate that the value must be evenly divisble by this value. |

### Arrays

| Method        | Parameter | Description                                                                          |
| ------------- | --------- | ------------------------------------------------------------------------------------ |
| `items`       | Schema    | All items in the array must match the sub-schema.                                    |
| `minItems`    | int       | The minimum number of items in the array.                                            |
| `maxItems`    | int       | The maximum number of items in the array.                                            |
| `uniqueItems` | -         | Enforce that all items in the array are unique. Uses `array_unique` for comparisons. |

### Objects

| Method                 | Parameter             | Description                                                                                          |
| ---------------------- | --------------------- | ---------------------------------------------------------------------------------------------------- |
| `properties`           | array<string, Schema> | Indicate the sub-schema for each object key.                                                         |
| `additionalProperties` | Schema\|false         | Set the schema of keys not provided by `properties`. Set to false to disallow additional properties. |
| `minProperties`        | int                   | The minimum number of properties in the array.                                                       |
| `maxProperties`        | int                   | The maximum number of properties in the array.                                                       |

## Documentation Methods

Some schema methods do not affect validation and are only provided to document the schema for discovery.

| Method        | Parameter    | Description                                                    |
| ------------- | ------------ | -------------------------------------------------------------- |
| `title`       | string       | Provide a descriptive title for the schema.                    |
| `description` | string       | Describe the field or attribute.                               |
| `examples`    | array<mixed> | Provide example values that the consumer could use.            |
| `deprecated`  | -            | Mark the value as deprecated.                                  |
| `readonly`    | -            | Fields marked as readonly are not included in the request.     |
| `writeonly`   | -            | Attributes marked as writeonly are not included in the output. |

## Schema Composition

You can use the `allOf`, `anyOf` & `oneOf` to compose multiple schemas together. It's recommended you use `Schema::empty()->allOf( ... )` so that the base schema type is clean.

## Validation Outside Schema

You'll sometimes need to validate your fields in ways that JSON schema can't accomdate for. Thankfully, there's an escape hatch: `custom`.

You can provide one (or an array) of Laravel's core validation rules to `custom` and any error messages will be passed along.

## Distinctions for JSON Schema

### Arrays

The following properties of arrays are not supported:

- `contains`
- `minContains` / `maxContains`
- `unevaluatedItems`
- prefixItems

### Objects

The following properties of arrays are not supported:

- `patternProperties`
- `unevaluatedProperties`
- `propertyNames`

Also note: `required` is not implemented on objects but is an extension to the base schema class, meaning any schema object can declare itself as required.

### String Formats

We support the following string formats:

- `date-time`
- `date`
- `time`
- `duration`
- `uri`
- `url`
- `ipv4`
- `ipv6`
- `email`

The following recommend formats are not supported by default:

- `idn-email`
- `hostname`
- `idn-hostname`
- `uuid`
- `uri-reference`
- `iri`
- `iri-reference`
- `uri-template`
- `json-pointer`
- `relative-json-pointer`
- `regex`

However, you can provide support for your own formats by using the static `WRD\Sleepy\Schema\Formats::registerFormat` method. This method takes the name of the format and a closure to validate the value. The closure must return strictly false to denote the provided value does not match the format, otherwise the value passes. Throwing an exception also fails the match.

# Attributes

Attributes allow you to define the schema of your model when it is outputted, as well as the fields available to be updated.

The `attributes` method should return an array of `WRD\Sleepy\Fields\Attributes`, where the key is the property on the Model class to pull the field from. See the example below for details.

```php
// App/Models/Post.php

use WRD\Sleepy\Fields\HasApi;
use WRD\Sleepy\Fields\Attributes\Attr;
//...

class Post extends Model
{
  use HasApi;

	// This defines the schema of your model.
	public static function attributes(): array {
    return [
      // Key is ignored for 'key' and 'basename'
      'id' => Attr::key(),
      'type' => Attr::basename(),

			// 'title' & 'body' pull their values from the models database columns.
      'title' => Attr::string()->required(),
			'body' => Attr::string()->required(),

			// 'thumbnail' pulls from an Eloquent attribute.
      'thumbnail' => Attr::string( 'url' )->readonly(),

			// 'creator' writes to a Eloquent relationship.
			// This field is writeonly by default, see `Attr::belongsTo` in Core Attributes.
			'creator' => Attr::belongsTo( User::class, 'creator_id' ),
    ];
  }
}
```

If you want an attribute to be outputted but not be written to, you can set it as `readonly`.

If you want an attribute to be writable to but not visible in the output, you can set it as `writeonly`.

## Core Attributes

Core attributes are a set of pre-built attributes that hide away the complexity of building queries. You can create your own complex attributes using the `WRD\Sleepy\Fields\Attribute` class or use on the pre-build attributes from the `WRD\Sleepy\Fields\Attr` class.

`Attr` has delegates the default Schema static functions, allowing you to call `Attr::string` rather than needing to pull in the main `Attribute` class.

`Attr::key()`
Gets the key of the model, using `Model::getKey()`.

`Attr::basename()`
Gets the base name of the model class, formatted to lower case. For example `WRD\App\Post` would become `post`.

`Attr::belongsTo( string $ownerModel, ?string $ownerKey = null )`
Allows you to change the owner ID of the model. These attributes are writeonly. If you want to surface the relationship in the output then you should use embedding.

# Embedding

Inspired by the HAL standard, you can use embedding to build requests the include information about related models.

```php
// App/Models/Post.php

use WRD\Sleepy\Fields\HasApi;

class Post extends Model{
	use HasApi; // Or just HasEmbed.

	public static function embeds(): array{
    return [
      // Sleepy will access the model's 'creator' relationship and include a reference to the foriegn model.
      'creator' => User::embed(),
    ];
  }
}
```

Links to the related models will be available under the `_links` key in the response. You can learn more about links in the [discovery section](#discovery).

To include the related model in the response, you can send the relationship name via the `_embed` query parameter. Multiple embeds can be provided, seperated by a comma. You can include nested relationships using dot notation.

For example,

```HTTP
POST https://example.com/api/v1/post/1?_embed=creator,creator.organisation
```

```json
{
	"id": 9,
	"type": "post",
	"_links": {
		"self": {
			"href": "https://sitepuppet.test/api/v1/post/9"
		},
    "creator": {
      "href": "https://sitepuppet.test/api/v1/user/1",
      "embeddable": true
    },
	},
	"_embedded": {
      "creator": {
			"id": 1,
			"type": "user",
			"name": "Kyle Cooper",
			"email": "mail@example.com",
			"_links" {
				"self": {
					"href": "https://sitepuppet.test/api/v1/user/1"
				},
				"organisation": {
					"href": "https://sitepuppet.test/api/v1/organisation/3",
					"embeddable": true
				},
			},
			"_embedded": {
				"id": 3,
				"type": "organisation",
				"name": "WRD",
				"url": "https://wrd.agency",
				"_links": {
					"self": {
						"href": "https://sitepuppet.test/api/v1/organisation/3"
					},
				}
			}
		}
	}
}
```

> Note: Our schema for links extends the HAL specification by including `embeddable`, a boolean field which indicates that a link can requested to be embedded.

## Changing the Default Names

You can change the name of `_links` attribute by overriding the static `getEmbedLinksAttributeName` method on your model.

You can change the name of `_embedded` attribute by overriding the static `getEmbedsAttributeName` method on your model.

You can change the name of `_embedded` query parameter field by overriding the static `getEmbedFieldsName` method on your model.

# Filtering

Consumers can apply filters by sending the value of the filter to the query parameter matching the key of the filters array.

```php
// App/Models/Post.php

use WRD\Sleepy\Fields\HasApi;
use WRD\Sleepy\Fields\Filters\Filters;
//...

class Post extends Model
{
  use HasApi; // Or just `HasFilters`.

	// Provides the allowed filters.
  public static function filters(): array{
    return [
		  // The key is the name of the filter.
			// For example, the consumer would include '?search=hello' to search the title & body columns.
      'search' => Filters::search('title,body'),
    ];
  }

	// ...
}
```

## Operators

Filters can have support for multiple operators, allowing more flexible querying from consumers.

Consumers can either provide the value directly or using an operator name as an array key.

See `WRD\Sleepy\Fields\Filters\Operator` for the list of operator keys.

```http
// Provide value directly.
GET https://example.com/api/v1/post?date=2025-02-01

// Provide an operator (greater than or equal to).
GET https://example.com/api/v1/post?date[gte]=2025-02-01
```

## Core Filters

Core filters are a set of pre-built filters that hide away the complexity of building queries. You can create your own complex filters using the `WRD\Sleepy\Fields\Filter` class or use on the pre-build filters from the `WRD\Sleepy\Fields\Filters` class.

`Filters::text( string $column )`
Matches a column exactly.
_Allowed operators: Equals (eq), Not Equals (neq)._

`Filters::numeric( string $column )`
Matches a column numerically.
_Allowed operators: Equals (eq), Not Equals (neq), Greater (gt), Greater Equals (gte), Lesser (lt), Lesser Equals (lte)._

`Filters::date( string $column )`
Matches a datetime column.
_Allowed operators: Equals (eq), Not Equals (neq), Greater (gt), Greater Equals (gte), Lesser (lt), Lesser Equals (lte)._

`Filters::cases( string $column, array $cases )`
Matches a column exactly, but only allowing specific values.
_Allowed operators: Equals (eq), Not Equals (neq)._

`Filters::search( string $columns )`
Fuzzy match a value. Multiple columns can be provided user commas to separate.
_Allowed operators: Equals (eq)._

`Filters::belongsTo( string $model, ?string $foreignKey = null )`
Show only models that belong to another. Consumers provide a string key for the owner model.
_Allowed operators: Equals (eq)._

# Sorting

Consumers can provide the `order_by` and `order` keys to set the ordering method and direction, respectively.

The value of the `order_by` query parameter must be a key from the `sorts()` array. This defaults to the first key of the array.

The query value for `order` can be either `asc` (ascending) or `desc` (descending), defaulting to `asc`.

```php
// App/Models/Post.php

use WRD\Sleepy\Fields\HasApi;
use WRD\Sleepy\Fields\Filters\Filters;
//...

class Post extends Model
{
  use HasApi; // Or just `HasSorts`.

	public static function sorts(): array{
    return [
      'title' => Sorts::alphabetical( "title" ),
			'published' => Sorts::date( "created_at" ),
    ];
  }
}
```

## Core Sorts

Core sorts are a set of pre-built sorts that hide away the complexity of building queries. You can create your own complex sorts using the `WRD\Sleepy\Fields\Sort` class or use on the pre-build sorts from the `WRD\Sleepy\Fields\Sorts` class.

`Sorts::alphabetical( string $column )`
Sort a column alphabetically.

`Sorts::numeric( string $column )`
Sort a column numerically.

`Sorts::date( string $column )`
Sort a by datetime.

`Sorts::cases( string $column, array $cases )`
Sort aa column in a specified order. The results will be returning in the order specified in the `$cases` array. Useful for enums.

# Pagination

The collection endpoint for a model is automatically paginated. By default, 10 results are shown per page but this can be changed using the `per_page` query variable. This is capped to 99 items per page.

Paginated results push the models into the `items` field. Metadata about the pagination, such as the total number of pages, can be accessed via the `meta` field.

> :question: Since `HasApi` includes `HasPagination`, your models collection endpoints will automatically be paginated. You can disable this by specifying the traits individually.

# Authentication & Authorization

## Model Shorthand

When using the `API::model()` shorthand, Sleepy will assume you have a standard Laravel policy for your model. It'll setup auth guards for `viewAll`, `create`, `view`, `update`, and `destroy`.

## Custom Routes

For routes you setup on your own, you'll need to make sure you include an authorization function. **By default, custom routes are public.** You can use the `auth` fluent method to provide an callback function to authorize the user.

> If you provide an auth callback then Sleepy will automatically ensure that the user is authenticated before handling your callback.

```php
API::endpoint( 'GET' )
	->action( function( ApiRequest $req ){
		// ...
	})
	->auth( function( ApiRequest $req, Model $model ) => {
		// The user has already been authenticated.
		// We just need to authorize them.
		return true;
	})
	->responses( 200, 401, 403 )
	// We should also make sure we document our responses.
	// Sleepy will return a HTTP 403 for unauthenticated responses and a 401 for unauthorized responses.
```

## Login Shorthand

Sleepy provides a `API::login` route out of the box as an easy starter method to authenticate requests. This provides a `GET` endpoint to check the authentication status, a `POST` endpoint to create an authenticated session and a `DELETE` endpoint to destory the session and log-out.

The login shorthand uses Laravel's default authentication system which is cookie based, making it stateful. If you want to use a basic stateless authentication system you could look into the [`auth.basic` middleware](https://laravel.com/docs/11.x/authentication#http-basic-authentication).

If you're looking for a more complex authentication system, such as API tokens, we'd recommend you reach for [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum#main-content) to help with the authentication side of your project.

# Discovery

Sleepy uses it's schema-driven approach to automatically generate discovery endpoints for your API.

## API Base / Group Discovery

A `GET` endpoint is automatically created at the URL of the API base and any groups you create that lists all of the available routes & endpoints beneath that node.

## Route / Endpoint Discovery

Every route includes a `OPTIONS` endpoint which details all of the other endpoints available at that route. It lists the available methods & fields for each route and, if available, the shared schema of the route's responses. For Model Routes, this will be the model's attribute schema.

## Authenticated Discovery

Routes are only included available for discovery depending on the authentication of the user.

If an endpoint is public, then it will always be available in discovery.

If an endpoint is not public (it has an authorization callback set), it will only be included if the user has authenticated themselves.

Routes & groups are only available to unauthenticated users if they have at least one public endpoint (the private endpoints are still hidden).

Authorization callbacks are not called to validate if an endpoint can be included in discovery.

> :warning: Remember! Custom routes in Sleepy are public by default, and will appear in the discovery endpoints for unauthenticated users unless you add an authorization endpoint.

## Linking

As show above, Sleepy will automatically populate the `_links` field of your model to include links to the API endpoints for related models.

Sleepy will also automatically provide links to the current model (`self`) and the collection of all models (`collection`).

You can control the names of the links field by overriding the static `getLinksAttributeName` method on your model for default links and the static `getEmbedLinksAttributeName` method for links to embedded models.

# Exception Handling

Sleepy will handle consistent JSON formatting of errors caused by unauthentication, unauthorization, 404s and field validation errors but it won't catch other exceptions.

# Commands

## List

You can use the list command to print all of the API routes and their fields to the console.

```
php artisan sleepy:list
```

## Markdown

You can automatically generate markdown documentation from your API using the following command. This will be outputted to the specified directory, relative to the root of your project (in this case '/docs').

```
php artisan sleepy:markdown /docs
```

> :warning: **This is an experimental feature.**

# Configuration

You can publish the configuration file into your project using the following command:

```
php artisan vendor:publish --provider=WRD\Sleepy\Providers\SleepyServiceProvider
```

Currently Sleepy provides two configuration options:

| Name                     | Type | Description                                                                                    |
| ------------------------ | ---- | ---------------------------------------------------------------------------------------------- |
| include_links_in_schema  | bool | Defaults to 'false'. Set to true to show the schema of links in the model schema.              |
| include_embeds_in_schema | bool | Defaults to false. Set to true to enable the schema of embeds in the provided in model schema. |
