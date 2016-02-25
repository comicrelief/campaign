<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsFieldTemplate\DsFieldTemplateInterface.
 */

namespace Drupal\ds\Plugin\DsFieldTemplate;

use Drupal\Core\Entity\EntityInterface;

/**
 * Base class for all the ds plugins.
 */
interface DsFieldTemplateInterface {

  /**
   * Lets you add you add additional form element for your layout.
   */
  public function alterForm(&$form);

  /**
   * Gets the entity this layout belongs too.
   */
  public function getEntity();

  /**
   * Sets the entity this layout belong too.
   */
  public function setEntity(EntityInterface $entity);

  /**
   * Massages the values before they get rendered.
   */
  public function massageRenderValues(&$field_settings, $values);

  /**
   * Gets the chosen theme function.
   */
  public function getThemeFunction();

  /**
   * Creates default configuration for the layout.
   */
  public function defaultConfiguration();

  /**
   * Get the selected configuration.
   */
  public function getConfiguration();

  /**
   * Set the configuration for this layout.
   */
  public function setConfiguration(array $configuration);

}
