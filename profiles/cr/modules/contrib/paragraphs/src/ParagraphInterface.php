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
 * @ingroup paragraphs
 */
interface ParagraphInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface
{

  /**
   * Gets the parent entity of the paragraph.
   *
   */
  public function getParentEntity();
}
