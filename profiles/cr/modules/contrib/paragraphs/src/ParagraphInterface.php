<?php

/**
 * @file
 * Contains Drupal\paragraphs\ParagraphInterface.
 */

namespace Drupal\paragraphs;

use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a paragraphs entity.
 * @ingroup account
 */
interface ParagraphInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface
{

  // Add get/set methods for your configuration properties here.
}
