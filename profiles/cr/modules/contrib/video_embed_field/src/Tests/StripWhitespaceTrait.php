<?php

/**
 * @file
 * Contains \Drupal\video_embed_field\Tests\StripWhitespaceTrait.
 */

namespace Drupal\video_embed_field\Tests;

trait StripWhitespaceTrait {
  /**
   * Remove HTML whitespace from a string.
   *
   * @param $string
   *   The input string.
   *
   * @return string
   *   The whitespace cleaned string.
   */
  protected function stripWhitespace($string) {
    $no_whitespace = preg_replace('/\s{2,}/', '', $string);
    $no_whitespace = str_replace("\n", '', $no_whitespace);
    return $no_whitespace;
  }
}
