<?php

/**
 * @file
 * Contains Drupal\pathauto\AliasCleaner
 */

namespace Drupal\pathauto;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides an alias cleaner.
 */
class AliasCleaner implements AliasCleanerInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The alias storage helper.
   *
   * @var AliasStorageHelperInterface
   */
  protected $aliasStorageHelper;

  /**
   * Alias max length.
   *
   * @var int
   */
  protected $aliasMaxLength;

  /**
   * Creates a new AliasCleaner.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\pathauto\AliasStorageHelperInterface $alias_storage_helper
   *   The alias storage helper.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AliasStorageHelperInterface $alias_storage_helper) {
    $this->configFactory = $config_factory;
    $this->aliasStorageHelper = $alias_storage_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function cleanAlias($alias) {
    if (!isset($this->aliasMaxLength)) {
      $config = $this->configFactory->get('pathauto.settings');
      $this->aliasMaxLength = min($config->get('max_length'), $this->aliasStorageHelper->getAliasSchemaMaxLength());
    }

    $output = $alias;

    // Trim duplicate, leading, and trailing separators. Do this before cleaning
    // backslashes since a pattern like "[token1]/[token2]-[token3]/[token4]"
    // could end up like "value1/-/value2" and if backslashes were cleaned first
    // this would result in a duplicate blackslash.
    $output = $this->getCleanSeparators($output);

    // Trim duplicate, leading, and trailing backslashes.
    $output = $this->getCleanSeparators($output, '/');

    // Shorten to a logical place based on word boundaries.
    $output = Unicode::truncate($output, $this->aliasMaxLength, TRUE);

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getCleanSeparators($string, $separator = NULL) {
    $config = $this->configFactory->get('pathauto.settings');

    if (!isset($separator)) {
      $separator = $config->get('separator');
    }

    $output = $string;

    if (strlen($separator)) {
      // Trim any leading or trailing separators.
      $output = trim($output, $separator);

      // Escape the separator for use in regular expressions.
      $seppattern = preg_quote($separator, '/');

      // Replace multiple separators with a single one.
      $output = preg_replace("/$seppattern+/", $separator, $output);

      // Replace trailing separators around slashes.
      if ($separator !== '/') {
        $output = preg_replace("/\/+$seppattern\/+|$seppattern\/+|\/+$seppattern/", "/", $output);
      }
      else {
        // If the separator is a slash, we need to re-add the leading slash
        // dropped by the trim function.
        $output = '/' . $output;
      }
    }

    return $output;
  }

}
