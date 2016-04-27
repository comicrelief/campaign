<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Tests\TaxonomyTestTrait.
 */

namespace Drupal\taxonomy\Tests;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Language\LanguageInterface;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Provides common helper methods for Taxonomy module tests.
 */
trait TaxonomyTestTrait {

  /**
   * Returns a new vocabulary with random properties.
   */
  function createVocabulary() {
    // Create a vocabulary.
    $vocabulary = entity_create('taxonomy_vocabulary', array(
      'name' => $this->randomMachineName(),
      'description' => $this->randomMachineName(),
      'vid' => Unicode::strtolower($this->randomMachineName()),
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'weight' => mt_rand(0, 10),
    ));
    $vocabulary->save();
    return $vocabulary;
  }

  /**
   * Returns a new term with random properties in vocabulary $vid.
   *
   * @param \Drupal\taxonomy\Entity\Vocabulary $vocabulary
   *   The vocabulary object.
   * @param array $values
   *   (optional) An array of values to set, keyed by property name. If the
   *   entity type has bundles, the bundle key has to be specified.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   The new taxonomy term object.
   */
  function createTerm(Vocabulary $vocabulary, $values = array()) {
    $filter_formats = filter_formats();
    $format = array_pop($filter_formats);
    $term = entity_create('taxonomy_term', $values + array(
      'name' => $this->randomMachineName(),
      'description' => array(
        'value' => $this->randomMachineName(),
        // Use the first available text format.
        'format' => $format->id(),
      ),
      'vid' => $vocabulary->id(),
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    ));
    $term->save();
    return $term;
  }
}
