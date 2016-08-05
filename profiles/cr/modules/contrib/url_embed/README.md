# URL Embed Module

[![Travis build status](https://img.shields.io/travis/drupal-media/url_embed/8.x-1.x.svg)](https://travis-ci.org/drupal-media/url_embed) [![Scrutinizer code quality](https://img.shields.io/scrutinizer/g/drupal-media/url_embed/8.x-1.x.svg)](https://scrutinizer-ci.com/g/drupal-media/url_embed)

[URL Embed](https://www.drupal.org/project/url_embed) module allows any URL to be embedded using a text editor.

## Requirements

* Drupal 8
* [Embed](https://www.drupal.org/project/embed) module
* [Embed](https://github.com/oscarotero/Embed) library

## Installation

URL Embed can be installed via the [standard Drupal installation process](http://drupal.org/node/895232).

## Configuration

* Install and enable [Embed](https://www.drupal.org/project/embed) module.
* Install and enable [URL Embed](https://www.drupal.org/project/url_embed) module.
* Go to the 'Text formats and editors' configuration page: `/admin/config/content/formats`, and for each text format/editor combo where you want to embed URLs, do the following:
  * Enable the 'Display embedded URLs' filter.
  * Drag and drop the 'URL' button into the Active toolbar.
  * If the text format uses the 'Limit allowed HTML tags and correct faulty HTML' filter, ensure the necessary tags and attributes are whitelisted: add ```<drupal-url data-embed-url data-url-provider>``` to the 'Allowed HTML tags' setting. (Will happen automatically after https://www.drupal.org/node/2554687.)

## Usage

* For example, create a new *Article* content.
* Click on the 'URL' button in the text editor.
* Enter the URL that you want to embed.
* Optionally, choose to align left, center or right.

## Embedding URLs without WYSIWYG

Users should be embedding URLs using the CKEditor WYSIWYG button as described above. This section is more technical about the HTML markup that is used to embed the actual URL.

```html
<drupal-url data-embed-url="https://www.youtube.com/watch?v=xxXXxxXxxxX" data-url-provider="YouTube" />
```
