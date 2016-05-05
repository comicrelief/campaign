<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormInterface.
 */

namespace Drupal\yamlform;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an interface defining a YAML form entity.
 */
interface YamlFormInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

  /**
   * Determine if the form has page or is attached to other entities.
   *
   * @return bool
   *   TRUE if the form is a page with dedicated path.
   */
  public function hasPage();

  /**
   * Determine if the form's inputs include a managed_file upload element.
   *
   * @return bool
   *   TRUE if the form's inputs include a managed_file upload element.
   */
  public function hasManagedFile();

  /**
   * Returns the YAML form opened status indicator.
   *
   * @return bool
   *   TRUE if the YAML form is open to new submissions.
   */
  public function isOpen();

  /**
   * Returns the YAML form closed status indicator.
   *
   * @return bool
   *   TRUE if the YAML form is closed to new submissions.
   */
  public function isClosed();


  /**
   * Checks if a YAML form has submissions.
   *
   * @return bool
   *   TRUE if the YAML form has submissions.
   */
  public function hasSubmissions();

  /**
   * Returns the YAML form settings.
   *
   * @return array
   *   A structured array containing all the YAML form settings.
   */
  public function getSettings();

  /**
   * Sets the YAML form settings.
   *
   * @param array $settings
   *   The structured array containing all the YAML form setting.
   *
   * @return $this
   */
  public function setSettings(array $settings);

  /**
   * Returns the YAML form settings for a given key.
   *
   * @param string $key
   *   The key of the setting to retrieve.
   *
   * @return mixed
   *   The settings value, or NULL if no settings exists.
   */
  public function getSetting($key);

  /**
   * Saves a YAML form setting for a given key.
   *
   * @param string $key
   *   The key of the setting to store.
   * @param mixed $value
   *   The data to store.
   */
  public function setSetting($key, $value);

  /**
   * Returns the YAML form access controls.
   *
   * @return array
   *   A structured array containing all the YAML form access controls.
   */
  public function getAccessRules();

  /**
   * Sets the YAML form access.
   *
   * @param array $access
   *   The structured array containing all the YAML form access controls.
   *
   * @return $this
   */
  public function setAccessRules(array $access);

  /**
   * Returns the YAML form default settings.
   *
   * @return array
   *   A structured array containing all the YAML form default settings.
   */
  public static function getDefaultSettings();

  /**
   * Returns the YAML form default access controls.
   *
   * @return array
   *   A structured array containing all the YAML form default access controls.
   */
  public static function getDefaultAccessRules();

  /**
   * Checks YAML form access to an operation on a YAML form's submission.
   *
   * @param string $operation
   *   The operation access should be checked for.
   *   Usually "create", "view", "update", "delete", "purge", or "admin".
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface|NULL $yamlform_submission
   *   (optional) A YAML form submission.
   *
   * @return bool
   *   The access result. Returns a TRUE is access is allowed.
   */
  public function checkAccessRules($operation, AccountInterface $account, YamlFormSubmissionInterface $yamlform_submission = NULL);

  /**
   * Get YAML form submission form.
   *
   * @param array $values
   *   (optional) An array of values to set, keyed by property name.
   *
   * @return array
   *   A render array representing a YAML form submission form.
   */
  public function getSubmissionForm(array $values = []);

  /**
   * Get inputs (YAML) value.
   *
   * @return string
   *   The inputs' raw value.
   */
  public function getInputsRaw();

  /**
   * Get original inputs (YAML) value.
   *
   * @return string|NULL
   *   The original inputs' raw value. Original inputs is NULL for new YAML
   *   forms.
   */
  public function getOriginalInputsRaw();

  /**
   * Get inputs (YAML) as an associative array.
   *
   * @return array|bool
   *   Inputs as an associative array. Returns FALSE is inputs YAML is invalid.
   */
  public function getInputs();

  /**
   * Get YAML form inputs flattened into an associative array.
   *
   * @return array
   *   YAML form inputs flattened into an associative array keyed by input name.
   */
  public function getFlattenedInputs();

  /**
   * Get YAML form flattened list of elements.
   *
   * @return array
   *   YAML form elements flattened into an associative array keyed by element name.
   */
  public function getElements();

  /**
   * Get YAML form element options.
   *
   * @return array
   *   An associative array of options.
   */
  public function getElementOptions(array $element);

  /**
   * Update submit and confirm paths (ie URL aliases) associated with this YAML form.
   */
  public function updatePaths();

  /**
   * Update submit and confirm paths associated with this YAML form.
   */
  public function deletePaths();

  /**
   * Returns a specific YAML form handler.
   *
   * @param string $handler_id
   *   The YAML form handler ID.
   *
   * @return \Drupal\yamlform\YamlFormHandlerInterface
   *   The YAML form handler object.
   */
  public function getHandler($handler_id);

  /**
   * Returns the YAML form handlers for this YAML form.
   *
   * @param string $plugin_id
   *   (optional) Plugin id used to return specific plugin instances
   *   (ie handlers).
   * @param bool $status
   *   (optional) Status used to return enabled or disabled plugin instances
   *   (ie handlers).
   *
   * @return \Drupal\yamlform\YamlFormHandlerPluginCollection|\Drupal\yamlform\YamlFormHandlerInterface[]
   *   The YAML form handler plugin collection.
   */
  public function getHandlers($plugin_id = NULL, $status = NULL);

  /**
   * Saves an YAML form handler for this YAML form.
   *
   * @param array $configuration
   *   An array of YAML form handler configuration.
   *
   * @return string
   *   The YAML form handler ID.
   */
  public function addYamlFormHandler(array $configuration);

  /**
   * Deletes an YAML form handler from this style.
   *
   * @param \Drupal\yamlform\YamlFormHandlerInterface $effect
   *   The YAML form handler object.
   *
   * @return $this
   */
  public function deleteYamlFormHandler(YamlFormHandlerInterface $effect);

  /**
   * Invoke a handlers method.
   *
   * @param string $method
   *   The handle method to be invoked.
   * @param mixed $data
   *   The argument to passed by reference to the handler method.
   */
  public function invokeHandlers($method, &$data, &$context1 = NULL, &$context2 = NULL);

  /**
   * Required to allow YAML form which are config entities to have an EntityViewBuilder.
   *
   * Prevents:
   *   Fatal error: Call to undefined method
   *   Drupal\yamlform\Entity\YamlForm::isDefaultRevision()
   *   in /private/var/www/sites/d8_dev/core/lib/Drupal/Core/Entity/EntityViewBuilder.php
   *   on line 169
   *
   * @see \Drupal\Core\Entity\RevisionableInterface::isDefaultRevision()
   *
   * @return bool
   *   Always return TRUE since config entities are not revisionable.
   */
  public function isDefaultRevision();

  /**
   * Returns the stored value for a given key in the YAML form's state.
   *
   * @param string $key
   *   The key of the data to retrieve.
   * @param mixed $default
   *   The default value to use if the key is not found.
   *
   * @return mixed
   *   The stored value, or NULL if no value exists.
   */
  public function getState($key, $default = NULL);
  /**
   * Saves a value for a given key in the YAML form's state.
   *
   * @param string $key
   *   The key of the data to store.
   * @param mixed $value
   *   The data to store.
   */
  public function setState($key, $value);

  /**
   * Deletes an item from the YAML form's state.
   *
   * @param string $key
   *   The item name to delete.
   */
  public function deleteState($key);

}
