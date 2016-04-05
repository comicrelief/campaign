<?php


/**
 * @file
 * Contains \Drupal\Paragraphs\Controller\ParagraphsPreviewController.
 */

namespace Drupal\paragraphs\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Controller\EntityViewController;

/**
 * Defines a controller to render a single paragraph.
 */
class ParagraphsPreviewController extends EntityViewController {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $paragraph, $view_mode_id = 'full', $langcode = NULL) {
		die($paragraph);
  }

}

  /**
   * The _title_callback for the page that renders a single paragraph.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node_preview
   *   The current node.
   *
   * @return string
   *   The page title.
   */
  public function title(EntityInterface $paragraphs) {
    return $this->entityManager->getTranslationFromContext($paragraphs)->label();
  }

}
