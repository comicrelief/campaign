<?php

namespace Drupal\cr_solr\Plugin\search_api\processor;

use Drupal\media_entity\MediaInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\file\Entity\File;

/**
 * Excludes media entities from node indexes.
 *
 * @SearchApiProcessor(
 *   id = "ignore_media_mp4",
 *   label = @Translation("Ignore background media"),
 *   description = @Translation("Don't index mp4 files."),
 *   stages = {
 *     "alter_items" = 0
 *   }
 * )
 */
class SearchApiExcludeEntityProcessor extends ProcessorPluginBase {


  /**
   * {@inheritdoc}
   */
  public static function supportsIndex(IndexInterface $index) {
    foreach ($index->getDatasources() as $datasource) {
      if ($datasource->getEntityTypeId() === 'media') {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {
    /** @var \Drupal\search_api\Item\ItemInterface $item */
    foreach ($items as $item_id => $item) {
      $object = $item->getOriginalObject()->getValue();
      if ($object instanceof MediaInterface) {
        if ($object->toArray()['bundle'][0]['target_id'] === 'cr_file') {
          $fid = $object->toArray()['field_cr_file'][0]['target_id'];
          //load file id
          $filename = File::load($fid)->getMimeType();
          if ($filename === 'video/mp4') {
            unset($items[$item_id]);
          }
        }
      }
    }
  }
}
