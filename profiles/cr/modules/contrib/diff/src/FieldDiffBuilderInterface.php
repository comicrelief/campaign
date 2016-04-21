<?php

/**
 * @file
 * Contains \Drupal\diff\FieldDiffBuilderInterface.
 */

namespace Drupal\diff;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

interface FieldDiffBuilderInterface extends PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Builds an array of strings.
   *
   * This method is responsible for transforming a FieldItemListInterface object
   * into an array of strings. The resulted array of strings is then compared by
   * the Diff component with another such array of strings and the result
   * represents the difference between two entity fields.
   *
   * Example of FieldItemListInterface built into an array of strings:
   * @code
   * array(
   *   0 => "This is an example string",
   *   1 => "Field values or properties",
   * )
   * @endcode
   *
   * @see \Drupal\diff\Plugin\Diff\TextFieldBuilder
   *
   * @param FieldItemListInterface $field_items
   *   Represents an entity field.
   *
   * @return mixed
   *   An array of strings to be compared. If an empty array is returned it
   *   means that a field is either empty or no properties need to be compared
   *   for that field.
   */
  public function build(FieldItemListInterface $field_items);

}
