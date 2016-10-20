<?php

namespace Drupal\jsonapi\Context;

/**
 * Contains FieldResolverInterface.
 *
 * Interface for mapping public field names to Drupal field names.
 */
interface FieldResolverInterface {

  /**
   * Maps a Drupal field name to a public field name.
   *
   * Example:
   *   'field_author.entity.field_first_name' -> 'author.firstName'.
   *
   * @param string $field_name
   *   The Drupal field name to map to a public field name.
   *
   * @return string
   *   The mapped field name.
   */
  public function resolveExternal($field_name);

  /**
   * Maps a public field name to a Drupal field name.
   *
   * Example:
   *   'author.firstName' -> 'field_author.entity.field_first_name'.
   *
   * @param string $field_name
   *   The publicI field name to map to a Drupal field name.
   *
   * @return string
   *   The mapped field name.
   */
  public function resolveInternal($field_name);

}
