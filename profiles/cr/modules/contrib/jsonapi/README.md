# JSON API
The jsonapi module exposes a [JSON API](http://jsonapi.org/) implementation for data stored in Drupal.

## Installation

Install the module as every other module.

## Compatibility

This module is compatible with Drupal core 8.2.x and higher.

## Configuration

Unlike the core REST module JSON API doesn't really require any kind of configuration by default.

## Usage

The jsonapi module exposes both config and content entity resources. On top of that it exposes one resource per bundle per entity. The default format appears like: `/api/{entity_type}/{bundle}/{uuid}?_format=api_json`

The list of endpoints then looks like the following:
* `/api/node/article?_format=api_json`: Exposes a collection of article content
* `/api/node/article/{UUID}?_format=api_json`: Exposes an individual article
* `/api/block?_format=api_json`: Exposes a collection of blocks
* `/api/block/{block}?_format=api_json`: Exposes an individual block
