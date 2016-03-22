<?php

/**
 * @file
 * Contains \Drupal\amp\Plugin\Field\FieldFormatter\AmpIframeFormatter.
 */

namespace Drupal\amp\Plugin\Field\FieldFormatter;

use Drupal\text\Plugin\Field\FieldFormatter\TextDefaultFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'amp_iframe' formatter.
 *
 * @FieldFormatter(
 *   id = "amp_iframe",
 *   label = @Translation("AMP Iframe"),
 *   description = @Translation("Display amp-iframe content."),
 *   field_types = {
 *     "string",
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   },
 * )
 */
class AmpIframeFormatter extends TextDefaultFormatter {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    // The AmpIframe text element extends that to pass #markup through the
    // amp library for processing an iframe into an amp-iframe.
    foreach ($items as $delta => $item) {
      $elements[$delta] = array(
        '#type' => 'amp_iframe',
        '#text' => $item->value,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      );
    }

    return $elements;
  }

}


