<?php

/**
 * @file
 * Contains \Drupal\purge\Plugin\Purge\TagsHeader\TagsHeadersServiceInterface.
 */

namespace Drupal\purge\Plugin\Purge\TagsHeader;

use Drupal\purge\ServiceInterface;

/**
 * Describes a service that provides access to available tags headers.
 */
interface TagsHeadersServiceInterface extends ServiceInterface, \Countable, \Iterator {}
