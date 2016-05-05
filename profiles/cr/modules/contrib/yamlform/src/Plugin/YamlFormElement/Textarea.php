<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\Textarea.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;
use Drupal\Component\Render\HtmlEscapedText;
/**
 * Provides a 'textarea' element.
 *
 * @YamlFormElement(
 *   id = "textarea",
 *   label = @Translation("Textarea"),
 *   multiline = TRUE,
 * )
 */
class Textarea extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    $build = [
      '#markup' => nl2br(new HtmlEscapedText($value)),
    ];
    return \Drupal::service('renderer')->renderPlain($build);
  }

}
