<?php

/**
 * @file
 * Contains \Drupal\purge\Plugin\Purge\TagsHeader\PurgeCacheTagsHeader.
 */

namespace Drupal\purge\Plugin\Purge\TagsHeader;

use Drupal\purge\Plugin\Purge\TagsHeader\TagsHeaderInterface;
use Drupal\purge\Plugin\Purge\TagsHeader\TagsHeaderBase;

/**
 * Sets and formats the default response header with cache tags.
 *
 * @PurgeTagsHeader(
 *   id = "purge",
 *   header_name = "Purge-Cache-Tags",
 * )
 */
class PurgeCacheTagsHeader extends TagsHeaderBase implements TagsHeaderInterface {}
