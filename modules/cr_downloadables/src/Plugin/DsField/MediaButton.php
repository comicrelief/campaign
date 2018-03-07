<?php

namespace Drupal\cr_downloadables\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\Core\Url;

/**
 * Media download button.
 *
 * A custom field to output media entities as a download button.
 *
 * @DsField(
 *   id = "cr_downloadables_MediaButton",
 *   title = @Translation("Download Button"),
 *   description = @Translation("Download Button"),
 *   entity_type = "media",
 *   provider = "cr_downloadables",
 *   ui_limit = {"cr_file|*"}
 * )
 */
class MediaButton extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->entity();

    $display_title = $entity->get('field_cr_field_display_title')->getValue();

    $file_id = $entity->get('field_cr_file')->getValue();
    $file = file_load($file_id[0]['target_id']);

    if($file) {
      $file_url = Url::fromUri(file_create_url($file->getFileUri()))->toString();

      return [
        '#markup' => '<a class="link link--red" href="' . $file_url . '" target=_blank >' . $display_title[0]['value'] . ' <span class="file-size">(' . format_size($file->getSize()) . ')</span></a>',
      ];
    } else {
      return [];
    };
  }

}
