<?php

namespace Drupal\cr_solr\Plugin\search_api\processor;

use Drupal\media_entity\MediaInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;

/**
 * Excludes media entities from node indexes.
 *
 * @SearchApiProcessor(
 *   id = "ignore_media_mp4",
 *   label = @Translation("Ignore background media"),
 *   description = @Translation("Don't index mp4 files."),
 *   stages = {
 *     "preprocess_index" = -50
 *   }
 * )
 */
class SearchApiExcludeEntityProcessor extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function preprocessIndexItems(array $items) {
    foreach ($items as $item_id => $item) {
      $object = $item->getOriginalObject()->getValue();
      if ($object instanceof MediaInterface) {
      }
    }
  }
}
