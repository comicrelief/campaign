<?php

namespace Drupal\yamlform;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;

/**
 * Defines an interface for form third party settings manager classes.
 */
interface YamlFormThirdPartySettingsManagerInterface extends ThirdPartySettingsInterface {

  /**
   * Wrapper for \Drupal\Core\Extension\ModuleHandlerInterface::alter.
   *
   * Loads all form third party settings before execute alter hooks.
   *
   * @see \Drupal\yamlform\YamlFormThirdPartySettingsManager::__construct
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL);

  /**
   * Build links to contrib modules that support form third party settings.
   *
   * @return array
   *   A renderable array of links to contrib modules that support form
   *   third party settings.
   */
  public function buildLinks();

  /**
   * Get contrib modules that support form third party settings.
   *
   * @return array
   *   An associative array of links keyed by module name.
   */
  public function getLinks();

  /**
   * Third party settings form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state);

  /**
   * Form element #after_build callback: Checks for 'third_party_settings'.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function afterBuild(array $form, FormStateInterface $form_state);

}
