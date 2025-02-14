<?php

return [
	/**
	 * @var bool include_links_in_schema
	 * 
	 * Set to false to hide the schema of links in the schema description of models.
	 * Defaults to 'false'.
	 * 
	 * These can be repetitive and not useful, since links are part of discovery anyway.
	 * This can help to reduce the size / clutter of responses to the schema endpoint.
	 * 
	 * This does not disable the "_links" parameter in the schema endpoint itself. These
	 * are the links which point to the route and it's parent collection.
	 */
	"include_links_in_schema" => false,

	/**
	 * @var bool include_embeds_in_schema
	 * 
	 * Controls whether the schema of embeds in the schema description of models.
	 * Defaults to 'false'.
	 * 
	 * Embed schema can balloon the size of the response for the schema endpoint,
	 * especially if embeds become deeply nested.
	 */
	"include_embeds_in_schema" => false,
];