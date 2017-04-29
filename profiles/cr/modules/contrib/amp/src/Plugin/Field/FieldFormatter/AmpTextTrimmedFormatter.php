<?php

/**
 * @file
 * Contains Drupal\amp\Plugin\Field\FieldFormatter\AmpTextTrimmedFormatter
 */

namespace Drupal\amp\Plugin\Field\FieldFormatter;

use Drupal\text\Plugin\Field\FieldFormatter\TextTrimmedFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Lullabot\AMP\AMP;
use Drupal;

/**
 * Plugin implementation of the 'amp_text_trimmed' formatter.
 *
 * @FieldFormatter(
 *   id = "amp_text_trimmed",
 *   label = @Translation("AMP Trimmed Text"),
 *   description = @Translation("Display AMP Trimmed text."),
 *   field_types = {
 *     "string",
 *     "text",
 *     "text_long",
 *     "text_with_summary"
 *   }
 * )
 */
class AmpTextTrimmedFormatter extends TextTrimmedFormatter {
  /**
   * Exactly like TextTrimmedFormatter except
   * '#type' => 'processed_text' was changed to:
   * '#type' => 'amp_processed_text'
   *
   * and 'text_summary_or_trimmed' was changed to 'amp_text_summary_or_trimmed'
   *
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    $render_as_summary = function (&$element) {
      // Make sure any default #pre_render callbacks are set on the element,
      // because text_pre_render_summary() must run last.
      $element += \Drupal::service('element_info')->getInfo($element['#type']);
      // Add the #pre_render callback that renders the text into a summary.
      $element['#pre_render'][] = '\Drupal\text\Plugin\field\FieldFormatter\TextTrimmedFormatter::preRenderSummary';
      // Pass on the trim length to the #pre_render callback via a property.
      $element['#text_summary_trim_length'] = $this->getSetting('trim_length');
    };

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $elements[$delta] = array(
          '#type' => 'amp_processed_text',
          '#text' => NULL,
          '#format' => $item->format,
          '#langcode' => $item->getLangcode(),
      );

      if ($this->getPluginId() == 'amp_text_summary_or_trimmed' && !empty($item->summary)) {
        $elements[$delta]['#text'] = $item->summary;
      }
      else {
        $elements[$delta]['#text'] = $item->value;
        $render_as_summary($elements[$delta]);
      }
    }

    return $elements;
  }
}


