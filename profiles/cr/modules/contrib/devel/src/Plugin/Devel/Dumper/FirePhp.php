<?php

/**
 * @file
 * Contains \Drupal\devel\Plugin\Devel\Dumper\FirePhp.
 */

namespace Drupal\devel\Plugin\Devel\Dumper;

use Drupal\devel\DevelDumperBase;

/**
 * Provides a FirePhp dumper plugin.
 *
 * @DevelDumper(
 *   id = "firephp",
 *   label = @Translation("FirePhp"),
 *   description = @Translation("Wrapper for <a href='http://www.firephp.org'>FirePhp</a> debugging tool.")
 * )
 */
class FirePhp extends DevelDumperBase {

  /**
   * {@inheritdoc}
   */
  public function dump($input, $name = NULL) {
    fb($input);
  }

  /**
   * {@inheritdoc}
   */
  public function export($input, $name = NULL) {
    $this->dump($input);
    return $this->t('Dump was redirected to the console.');
  }

  /**
   * {@inheritdoc}
   */
  public static function checkRequirements() {
    return class_exists('FirePHP', FALSE);
  }

}
