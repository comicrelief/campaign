<?php

/**
 * @file
 * Contains \Drupal\video_embed_wyswiyg\Plugin\CKEditorPlugin\VideoEmbedWysiwyg.
 */


namespace Drupal\video_embed_wysiwyg\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * @CKEditorPlugin(
 *   id = "video_embed",
 *   label = @Translation("Video Embed WYSIWYG")
 * )
 */
class VideoEmbedWysiwyg extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'video_embed_wysiwyg') . '/plugin/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'video_embed' => [
        'label' => $this->t('Video Embed'),
        'image' => drupal_get_path('module', 'video_embed_wysiwyg') . '/plugin/icon.png',
      ],
    ];
  }

  public function getConfig(Editor $editor) {
    return [];
  }

}
