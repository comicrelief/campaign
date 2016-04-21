<?php

namespace Drupal\Core\Config;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * An exception thrown if configuration has unmet dependencies.
 */
class UnmetDependenciesException extends ConfigException {

  /**
   * A list of configuration objects that have unmet dependencies.
   *
   * @var array
   */
  protected $configObjects = [];

  /**
   * The name of the extension that is being installed.
   *
   * @var string
   */
  protected $extension;

  /**
   * Gets the list of configuration objects that have unmet dependencies.
   *
   * @return array
   *   A list of configuration objects that have unmet dependencies.
   */
  public function getConfigObjects() {
    return $this->configObjects;
  }

  /**
   * Gets the name of the extension that is being installed.
   *
   * @return string
   *   The name of the extension that is being installed.
   */
  public function getExtension() {
    return $this->extension;
  }

  /**
   * Gets a translated message from the exception.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   *
   * @return string
   */
  public function getTranslatedMessage(TranslationInterface $string_translation, $extension) {
    return $string_translation->translate(
      'Unable to install @extension due to unmet dependencies: @config_names',
      [
        '@config_names' => self::formatConfigObjectList($this->configObjects),
        '@extension' => $extension,
      ]
    );
  }

  /**
   * Creates an exception for an extension and a list of configuration objects.
   *
   * @param $extension
   *   The name of the extension that is being installed.
   * @param array $config_objects
   *   A list of configuration object names that have unmet dependencies
   *
   * @return \Drupal\Core\Config\PreExistingConfigException
   */
  public static function create($extension, array $config_objects) {
    $message = SafeMarkup::format('Configuration objects provided by @extension have unmet dependencies: @config_names',
      array(
        '@config_names' => self::formatConfigObjectList($config_objects),
        '@extension' => $extension
      )
    );
    $e = new static($message);
    $e->configObjects = $config_objects;
    $e->extension = $extension;
    return $e;
  }

  /**
   * Formats a list of configuration objects.
   *
   * @param array $config_objects
   *   A list of configuration object names that have unmet dependencies
   *
   * @return string
   */
  protected static function formatConfigObjectList($config_objects) {
    $list = array();
    foreach ($config_objects as $config_object => $missing_dependencies) {
      $list[] = $config_object . ' (' . implode(', ', $missing_dependencies) .')';
    }
    return implode(', ', $list);
  }

}
