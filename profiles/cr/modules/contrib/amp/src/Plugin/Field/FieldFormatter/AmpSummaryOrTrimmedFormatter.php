<?php

/**
 * @file
 * Contains \Drupal\amp\Plugin\Field\FieldFormatter\AmpSummaryOrTrimmedFormatter
 */

namespace Drupal\amp\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'amp_text_summary_or_trimmed' formatter.
 *
 * @FieldFormatter(
 *   id = "amp_text_summary_or_trimmed",
 *   label = @Translation("AMP Summary or Trimmed"),
 *   description = @Translation("Display AMP Summary or Trimmed text."),
 *   field_types = {
 *     "string",
 *     "text_with_summary"
 *   }
 * )
 */
class AmpSummaryOrTrimmedFormatter extends AmpTextTrimmedFormatter { }


