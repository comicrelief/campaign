<?php

namespace Drupal\video_embed_field\Plugin\video_embed_field\Provider;

use Drupal\video_embed_field\ProviderPluginBase;

/**
 * A Vimeo provider plugin.
 *
 * @VideoEmbedProvider(
 *   id = "vimeo",
 *   title = @Translation("Vimeo")
 * )
 */
class Vimeo extends ProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function renderEmbedCode($width, $height, $autoplay) {
    return [
      '#type' => 'video_embed_iframe',
      '#provider' => 'vimeo',
      '#url' => sprintf('https://player.vimeo.com/video/%s', $this->getVideoId()),
      '#query' => [
        'autoplay' => $autoplay,
      ],
      '#attributes' => [
        'width' => $width,
        'height' => $height,
        'frameborder' => '0',
        'allowfullscreen' => 'allowfullscreen',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteThumbnailUrl() {
    return $this->oEmbedData()->thumbnail_large;
  }

  /**
   * Get the vimeo oembed data.
   *
   * @return array
   *   An array of data from the oembed endpoint.
   */
  protected function oEmbedData() {
    return json_decode(file_get_contents('http://vimeo.com/api/v2/video/' . $this->getVideoId() . '.json'))[0];
  }

  /**
   * {@inheritdoc}
   */
  public static function getIdFromInput($input) {
    preg_match('/^https?:\/\/(www\.)?vimeo.com\/(channels\/[a-zA-Z0-9]*\/)?(?<id>[0-9]*)(\/[a-zA-Z0-9]+)?$/', $input, $matches);
    return isset($matches['id']) ? $matches['id'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->oEmbedData()->title;
  }

}
