<?php

namespace Drupal\context;

use Drupal\Core\Executable\ExecutableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

interface ContextReactionInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface, ExecutableInterface {

  /**
   * Get the unique ID of this context reaction.
   *
   * @return string|null
   */
  public function getId();

  /**
   * Provides a human readable summary of the condition's configuration.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function summary();
}
