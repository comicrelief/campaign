<?php

namespace Drupal\yamlform;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a form entity.
 */
interface YamlFormInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface, EntityOwnerInterface {

  /**
   * Determine if the form has page or is attached to other entities.
   *
   * @return bool
   *   TRUE if the form is a page with dedicated path.
   */
  public function hasPage();

  /**
   * Determine if the form's elements include a managed_file upload element.
   *
   * @return bool
   *   TRUE if the form's elements include a managed_file upload element.
   */
  public function hasManagedFile();

  /**
   * Determine if the form is using a Flexbox layout.
   *
   * @return bool
   *   TRUE if if the form is using a Flexbox layout.
   */
  public function hasFlexboxLayout();

  /**
   * Returns the form opened status indicator.
   *
   * @return bool
   *   TRUE if the form is open to new submissions.
   */
  public function isOpen();

  /**
   * Returns the form closed status indicator.
   *
   * @return bool
   *   TRUE if the form is closed to new submissions.
   */
  public function isClosed();

  /**
   * Returns the form template indicator.
   *
   * @return bool
   *   TRUE if the form is a template and available for duplication.
   */
  public function isTemplate();

  /**
   * Returns the form confidential indicator.
   *
   * @return bool
   *   TRUE if the form is confidential .
   */
  public function isConfidential();

  /**
   * Checks if a form has submissions.
   *
   * @return bool
   *   TRUE if the form has submissions.
   */
  public function hasSubmissions();

  /**
   * Determine if the current form is translated.
   *
   * @return bool
   *   TRUE if the current form is translated.
   */
  public function hasTranslations();

  /**
   * Returns the form's description.
   *
   * @return string
   *   A form's description.
   */
  public function getDescription();

  /**
   * Sets a form's description.
   *
   * @param string $description
   *   A description.
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Returns the form settings.
   *
   * @return array
   *   A structured array containing all the form settings.
   */
  public function getSettings();

  /**
   * Sets the form settings.
   *
   * @param array $settings
   *   The structured array containing all the form setting.
   *
   * @return $this
   */
  public function setSettings(array $settings);

  /**
   * Returns the form settings for a given key.
   *
   * @param string $key
   *   The key of the setting to retrieve.
   *
   * @return mixed
   *   The settings value, or NULL if no settings exists.
   */
  public function getSetting($key);

  /**
   * Saves a form setting for a given key.
   *
   * @param string $key
   *   The key of the setting to store.
   * @param mixed $value
   *   The data to store.
   *
   * @return $this
   */
  public function setSetting($key, $value);

  /**
   * Returns the form access controls.
   *
   * @return array
   *   A structured array containing all the form access controls.
   */
  public function getAccessRules();

  /**
   * Sets the form access.
   *
   * @param array $access
   *   The structured array containing all the form access controls.
   *
   * @return $this
   */
  public function setAccessRules(array $access);

  /**
   * Returns the form default settings.
   *
   * @return array
   *   A structured array containing all the form default settings.
   */
  public static function getDefaultSettings();

  /**
   * Returns the form default access controls.
   *
   * @return array
   *   A structured array containing all the form default access controls.
   */
  public static function getDefaultAccessRules();

  /**
   * Checks form access to an operation on a form's submission.
   *
   * @param string $operation
   *   The operation access should be checked for.
   *   Usually "create", "view", "update", "delete", "purge", or "admin".
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   * @param \Drupal\yamlform\YamlFormSubmissionInterface|null $yamlform_submission
   *   (optional) A form submission.
   *
   * @return bool
   *   The access result. Returns a TRUE if access is allowed.
   */
  public function checkAccessRules($operation, AccountInterface $account, YamlFormSubmissionInterface $yamlform_submission = NULL);

  /**
   * Get form submission form.
   *
   * @param array $values
   *   (optional) An array of values to set, keyed by property name.
   * @param string $operation
   *   (optional) The operation identifying the form variation to be returned.
   *   Defaults to 'default'. This is typically used in routing.
   *
   * @return array
   *   A render array representing a form submission form.
   */
  public function getSubmissionForm(array $values = [], $operation = 'default');

  /**
   * Get elements (YAML) value.
   *
   * @return string
   *   The elements raw value.
   */
  public function getElementsRaw();

  /**
   * Get original elements (YAML) value.
   *
   * @return string|null
   *   The original elements' raw value. Original elements is NULL for new YAML
   *   forms.
   */
  public function getElementsOriginalRaw();

  /**
   * Get form elements decoded as an associative array.
   *
   * @return array|bool
   *   Elements as an associative array. Returns FALSE is elements YAML is invalid.
   */
  public function getElementsDecoded();

  /**
   * Set element properties.
   *
   * @param string $key
   *   The element's key.
   * @param array $properties
   *   An associative array of properties.
   * @param string $parent_key
   *   (optional) The element's parent key. Only used for new elements.
   *
   * @return $this
   */
  public function setElementProperties($key, array $properties, $parent_key = '');

  /**
   * Remove an element.
   *
   * @param string $key
   *   The element's key.
   */
  public function deleteElement($key);

  /**
   * Get form elements initialized as an associative array.
   *
   * @return array|bool
   *   Elements as an associative array. Returns FALSE is elements YAML is invalid.
   */
  public function getElementsInitialized();

  /**
   * Get form raw elements decoded and flattened into an associative array.
   *
   * @return array
   *   Form raw elements decoded and flattened into an associative array
   *   keyed by element name. Returns FALSE is elements YAML is invalid.
   */
  public function getElementsDecodedAndFlattened();

  /**
   * Get form elements initialized and flattened into an associative array.
   *
   * @return array
   *   Form elements flattened into an associative array keyed by element name.
   *   Returns FALSE is elements YAML is invalid.
   */
  public function getElementsInitializedAndFlattened();

  /**
   * Get form flattened list of elements.
   *
   * @return array
   *   Form elements flattened into an associative array keyed by element name.
   */
  public function getElementsFlattenedAndHasValue();

  /**
   * Get form elements selectors as options.
   *
   * @return array
   *   Form elements selectors as options.
   */
  public function getElementsSelectorOptions();

  /**
   * Sets elements (YAML) value.
   *
   * @param array $elements
   *   An renderable array of elements.
   *
   * @return $this
   */
  public function setElements(array $elements);

  /**
   * Get a form's initialized element.
   *
   * @param string $key
   *   The element's key.
   *
   * @return array|null
   *   An associative array containing an initialized element.
   */
  public function getElement($key);

  /**
   * Get a form's raw (uninitialized) element.
   *
   * @param string $key
   *   The element's key.
   *
   * @return array|null
   *   An associative array containing an raw (uninitialized) element.
   */
  public function getElementDecoded($key);

  /**
   * Get form wizard pages.
   *
   * @return array
   *   An associative array of form pages.
   */
  public function getPages();

  /**
   * Get form wizard page.
   *
   * @param string|int $index
   *   The name or index of a form wizard page.
   *
   * @return array|null
   *   A form wizard page element.
   */
  public function getPage($index);

  /**
   * Update submit and confirm paths (ie URL aliases) associated with this form.
   */
  public function updatePaths();

  /**
   * Update submit and confirm paths associated with this form.
   */
  public function deletePaths();

  /**
   * Returns a specific form handler.
   *
   * @param string $handler_id
   *   The form handler ID.
   *
   * @return \Drupal\yamlform\YamlFormHandlerInterface
   *   The form handler object.
   */
  public function getHandler($handler_id);

  /**
   * Returns the form handlers for this form.
   *
   * @param string $plugin_id
   *   (optional) Plugin id used to return specific plugin instances
   *   (ie handlers).
   * @param bool $status
   *   (optional) Status used to return enabled or disabled plugin instances
   *   (ie handlers).
   *
   * @return \Drupal\yamlform\YamlFormHandlerPluginCollection|\Drupal\yamlform\YamlFormHandlerInterface[]
   *   The form handler plugin collection.
   */
  public function getHandlers($plugin_id = NULL, $status = NULL);

  /**
   * Saves an form handler for this form.
   *
   * @param array $configuration
   *   An array of form handler configuration.
   *
   * @return string
   *   The form handler ID.
   */
  public function addYamlFormHandler(array $configuration);

  /**
   * Deletes an form handler from this style.
   *
   * @param \Drupal\yamlform\YamlFormHandlerInterface $effect
   *   The form handler object.
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
   * Invoke elements method.
   *
   * @param string $method
   *   The handle method to be invoked.
   * @param mixed $data
   *   The argument to passed by reference to the handler method.
   */
  public function invokeElements($method, &$data, &$context1 = NULL, &$context2 = NULL);

  /**
   * Required to allow form which are config entities to have an EntityViewBuilder.
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
   * Returns the stored value for a given key in the form's state.
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
   * Saves a value for a given key in the form's state.
   *
   * @param string $key
   *   The key of the data to store.
   * @param mixed $value
   *   The data to store.
   */
  public function setState($key, $value);

  /**
   * Deletes an item from the form's state.
   *
   * @param string $key
   *   The item name to delete.
   */
  public function deleteState($key);

  /**
   * Determine if the stored value for a given key exists in the form's state.
   *
   * @param string $key
   *   The key of the data to retrieve.
   *
   * @return bool
   *   TRUE if the  stored value for a given key exists
   */
  public function hasState($key);

}
