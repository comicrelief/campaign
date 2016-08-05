<?php

/**
 * @file
 * Contains \Drupal\url_embed\Plugin\CKEditorPlugin\DrupalUrl.
 */

namespace Drupal\url_embed\Plugin\CKEditorPlugin;

use Drupal\editor\Entity\Editor;
use Drupal\embed\EmbedCKEditorPluginBase;

/**
 * Defines the "drupalurl" plugin.
 *
 * @CKEditorPlugin(
 *   id = "drupalurl",
 *   label = @Translation("URL"),
 *   embed_type_id = "url"
 * )
 */
class DrupalUrl extends EmbedCKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'url_embed') . '/js/plugins/drupalurl/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return array(
      'DrupalUrl_dialogTitleAdd' => t('Insert Url'),
      'DrupalUrl_dialogTitleEdit' => t('Edit Url'),
      'DrupalUrl_buttons' => $this->getButtons(),
    );
  }

}
