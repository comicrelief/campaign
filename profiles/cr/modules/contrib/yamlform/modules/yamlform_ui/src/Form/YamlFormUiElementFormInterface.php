<?php

namespace Drupal\yamlform_ui\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Provides an interface for form element form.
 */
interface YamlFormUiElementFormInterface extends FormInterface, ContainerInjectionInterface {

  /**
   * Is new element.
   *
   * @return bool
   *   TRUE if this form generating a new element.
   */
  public function isNew();

  /**
   * Return the form associated with this form.
   *
   * @return \Drupal\yamlform\YamlFormInterface
   *   A form
   */
  public function getYamlForm();

  /**
   * Return the form element associated with this form.
   *
   * @return \Drupal\yamlform\YamlFormElementInterface
   *   A form element.
   */
  public function getYamlFormElement();

}
