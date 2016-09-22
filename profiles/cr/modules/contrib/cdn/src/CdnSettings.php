<?php

namespace Drupal\cdn;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigValueException;

/**
 * Wraps the CDN settings configuration, contains all parsing.
 */
class CdnSettings {

  /**
   * The CDN settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $rawSettings;

  /**
   * The lookup table.
   *
   * @var array|null
   */
  protected $lookupTable;

  /**
   * Constructs a new CdnSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->rawSettings = $config_factory->get('cdn.settings');
    $this->lookupTable = NULL;
  }

  /**
   * @return bool
   */
  public function isEnabled() {
    return $this->rawSettings->get('status') === TRUE;
  }

  /**
   * @return bool
   */
  public function farfutureIsEnabled() {
    return $this->rawSettings->get('farfuture.status') === TRUE;
  }

  /**
   * Returns the lookup table.
   *
   * @return array
   *   A lookup table. Keys are lowercase file extensions or the asterisk.
   *   Values are CDN domains (either string if only one, or array of strings if
   *   multiple).
   */
  public function getLookupTable() {
    if ($this->lookupTable === NULL) {
      $this->lookupTable = $this->buildLookupTable($this->rawSettings->get('mapping'));
    }
    return $this->lookupTable;
  }

  /**
   * Returns all unique CDN domains that are configured.
   *
   * @return string[]
   */
  public function getDomains() {
    $flattened = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->getLookupTable())), FALSE);
    $unique_domains = array_unique($flattened);
    return $unique_domains;
  }

  /**
   * Builds a lookup table: file extension to CDN domain(s).
   *
   * @param array $mapping
   *   An array matching either of the mappings in cdn.mapping.schema.yml.
   *
   * @return array
   *   A lookup table. Keys are lowercase file extensions or the asterisk.
   *   Values are CDN domains (either string if only one, or array of strings if
   *   multiple).
   *
   * @throws \Drupal\Core\Config\ConfigValueException
   *
   * @todo Abstract this out further in the future if the need arises, i.e. if
   *       more conditions besides extensions are added. For now, KISS.
   */
  protected function buildLookupTable(array $mapping) {
    $lookup_table = [];
    if ($mapping['type'] === 'simple') {
      $domain = $mapping['domain'];
      assert('strpos($domain, "/") === FALSE && strpos($domain, ":") === FALSE', "The provided domain $domain is not a valid domain. Provide domains or hostnames of the form 'cdn.com', 'cdn.example.com'. IP addresses and ports are also allowed.");
      if (empty($mapping['conditions'])) {
        $lookup_table['*'] = $domain;
      }
      else {
        if (empty($mapping['conditions']['extensions'])) {
          $lookup_table['*'] = $domain;
        }
        else {
          foreach ($mapping['conditions']['extensions'] as $extension) {
            $lookup_table[$extension] = $domain;
          }
        }
      }
    }
    elseif ($mapping['type'] === 'complex') {
      $fallback_domain = NULL;
      if (isset($mapping['fallback_domain'])) {
        $fallback_domain = $mapping['fallback_domain'];
        assert('strpos($fallback_domain, "/") === FALSE && strpos($fallback_domain, ":") === FALSE', "The provided fallback domain $fallback_domain is not a valid domain. Provide domains or hostnames of the form 'cdn.com', 'cdn.example.com'. IP addresses and ports are also allowed.");
        $lookup_table['*'] = $fallback_domain;
      }
      foreach ($mapping['domains'] as $nested_mapping) {
        $lookup_table += $this->buildLookupTable($nested_mapping);
      }
    }
    elseif ($mapping['type'] === 'auto-balanced') {
      if (empty($mapping['conditions']) || empty($mapping['conditions']['extensions'])) {
        throw new ConfigValueException('It does not make sense to apply auto-balancing to all files, regardless of extension.');
      }
      $domains = $mapping['domains'];
      foreach ($domains as $domain) {
        assert('strpos($domain, "/") === FALSE && strpos($domain, ":") === FALSE', "The provided domain $domain is not a valid domain. Provide domains or hostnames of the form 'cdn.com', 'cdn.example.com'. IP addresses and ports are also allowed.");
      }
      foreach ($mapping['conditions']['extensions'] as $extension) {
        $lookup_table[$extension] = $domains;
      }
    }
    else {
      throw new ConfigValueException('Unknown CDN mapping type specified.');
    }
    return $lookup_table;
  }

}
