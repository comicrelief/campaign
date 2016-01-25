<?php

/**
 * @file
 * Contains \Drupal\text_icon\Plugin\Field\FieldFormatter\TextIconFormatter.
 */

namespace Drupal\text_icon\Plugin\Field\FieldFormatter;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;

/**
 * Plugin implementation of the 'text_icon' formatter.
 *
 * @FieldFormatter(
 *   id = "text_icon",
 *   label = @Translation("Text icon"),
 *   field_types = {
 *     "string",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class TextIconFormatter extends StringFormatter {

  /**
   * {@inheritdoc}
   */
  protected function viewValue(FieldItemInterface $item) {
    return [
      '#markup' => new FormattableMarkup('<i class="@icon"></i>', ['@icon' => $item->value]),
    ];
  }

}
