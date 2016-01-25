<?php
/**
 * @file
 * Contains Drupal\pathauto\PathautoManagerInterface
 */

namespace Drupal\pathauto;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Provides and interface for PathautoManager.
 */
interface PathautoManagerInterface {

  /**
   * "Do nothing. Leave the old alias intact."
   */
  const UPDATE_ACTION_NO_NEW = 0;

  /**
   * "Create a new alias. Leave the existing alias functioning."
   */
  const UPDATE_ACTION_LEAVE = 1;

  /**
   * "Create a new alias. Delete the old alias."
   */
  const UPDATE_ACTION_DELETE = 2;

  /**
   * Remove the punctuation from the alias.
   */
  const PUNCTUATION_REMOVE = 0;

  /**
   * Replace the punctuation with the separator in the alias.
   */
  const PUNCTUATION_REPLACE = 1;

  /**
   * Leave the punctuation as it is in the alias.
   */
  const PUNCTUATION_DO_NOTHING = 2;

  /**
   * Resets internal caches.
   */
  public function resetCaches();

  /**
   * Clean up a string segment to be used in an URL alias.
   *
   * Performs the following possible alterations:
   * - Remove all HTML tags.
   * - Process the string through the transliteration module.
   * - Replace or remove punctuation with the separator character.
   * - Remove back-slashes.
   * - Replace non-ascii and non-numeric characters with the separator.
   * - Remove common words.
   * - Replace whitespace with the separator character.
   * - Trim duplicate, leading, and trailing separators.
   * - Convert to lower-case.
   * - Shorten to a desired length and logical position based on word boundaries.
   *
   * This function should *not* be called on URL alias or path strings
   * because it is assumed that they are already clean.
   *
   * @param string $string
   *   A string to clean.
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the Pathauto
   *   clean string replacement process. Supported options are:
   *   - langcode: A language code to be used when translating strings.
   *
   * @return string
   *   The cleaned string.
   */
  public function cleanString($string, array $options = array());

  /**
   * Load an URL alias pattern by entity, bundle, and language.
   *
   * @param $entity_type_id
   *   An entity (e.g. node, taxonomy, user, etc.)
   * @param $bundle
   *   A bundle (e.g. content type, vocabulary ID, etc.)
   * @param $language
   *   A language code, defaults to the LANGUAGE_NONE constant.
   */
  public function getPatternByEntity($entity_type_id, $bundle = '', $language = LanguageInterface::LANGCODE_NOT_SPECIFIED);

  /**
   * Apply patterns to create an alias.
   *
   * @param string $module
   *   The name of your module (e.g., 'node').
   * @param string $op
   *   Operation being performed on the content being aliased
   *   ('insert', 'update', 'return', or 'bulkupdate').
   * @param string $source
   *   An internal Drupal path to be aliased.
   * @param array $data
   *   An array of keyed objects to pass to token_replace(). For simple
   *   replacement scenarios 'node', 'user', and others are common keys, with an
   *   accompanying node or user object being the value. Some token types, like
   *   'site', do not require any explicit information from $data and can be
   *   replaced even if it is empty.
   * @param string $type
   *   For modules which provided pattern items in hook_pathauto(),
   *   the relevant identifier for the specific item to be aliased
   *   (e.g., $node->type).
   * @param string $langcode
   *   A string specify the path's language.
   *
   * @return array|string
   *   The alias that was created.
   *
   * @see _pathauto_set_alias()
   */
  public function createAlias($module, $op, $source, $data, $type = NULL, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED);

  /**
   * Return an array of arrays for punctuation values.
   *
   * Returns an array of arrays for punctuation values keyed by a name, including
   * the value and a textual description.
   * Can and should be expanded to include "all" non text punctuation values.
   *
   * @return array
   *   An array of arrays for punctuation values keyed by a name, including the
   *   value and a textual description.
   */
  public function getPunctuationCharacters();

  /**
   * Creates or updates an alias for the given entity.
   *
   * @param EntityInterface $entity
   *   Entity for which to update the alias.
   * @param string $op
   *   The operation performed (insert, update)
   * @param array $options
   *   - force: will force updating the path
   *   - language: the language for which to create the alias
   *
   * @return array|null
   *   - An array with alias data in case the alias has been created or updated.
   *   - NULL if no operation performed.
   */
  public function updateAlias(EntityInterface $entity, $op, array $options = array());

  /**
   * Clean tokens so they are URL friendly.
   *
   * @param array $replacements
   *   An array of token replacements
   *   that need to be "cleaned" for use in the URL.
   * @param array $data
   *   An array of objects used to generate the replacements.
   * @param array $options
   *   An array of options used to generate the replacements.
   */
  public function cleanTokenValues(&$replacements, $data = array(), $options = array());

}
