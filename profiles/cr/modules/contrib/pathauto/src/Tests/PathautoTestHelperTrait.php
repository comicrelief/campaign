<?php

/**
 * @file
 * Contains \Drupal\pathauto\Tests\PathautoTestHelperTrait.
 */

namespace Drupal\pathauto\Tests;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\pathauto\Entity\PathautoPattern;
use Drupal\pathauto\PathautoPatternInterface;
use Drupal\taxonomy\VocabularyInterface;

/**
 * Helper test class with some added functions for testing.
 */
trait PathautoTestHelperTrait {

  /**
   * Creates a pathauto pattern.
   *
   * @param string $entity_type_id
   *   The entity type.
   * @param string $pattern
   *   The path pattern.
   * @param int $weight
   *   (optional) The pattern weight.
   *
   * @return \Drupal\pathauto\PathautoPatternInterface
   *   The created pattern.
   */
  protected function createPattern($entity_type_id, $pattern, $weight = 10) {
    $pattern = PathautoPattern::create([
      'id' => Unicode::strtolower($this->randomMachineName()),
      'type' => 'canonical_entities:' . $entity_type_id,
      'pattern' => $pattern,
      'weight' => $weight,
    ]);
    $pattern->save();
    return $pattern;
  }

  /**
   * Add a bundle condition to a pathauto pattern.
   *
   * @param \Drupal\pathauto\PathautoPatternInterface $pattern
   *   The pattern.
   * @param string $entity_type
   *   The entity type ID.
   * @param string $bundle
   *   The bundle
   */
  protected function addBundleCondition(PathautoPatternInterface $pattern, $entity_type, $bundle) {
    $plugin_id = $entity_type == 'node' ? 'node_type' : 'entity_bundle:' . $entity_type;

    $pattern->addSelectionCondition(
      [
        'id' => $plugin_id,
        'bundles' => [
          $bundle => $bundle,
        ],
        'negate' => FALSE,
        'context_mapping' => [
          $entity_type => $entity_type,
        ]
      ]
    );
  }

  public function assertToken($type, $object, $token, $expected) {
    $bubbleable_metadata = new BubbleableMetadata();
    $tokens = \Drupal::token()->generate($type, array($token => $token), array($type => $object), [], $bubbleable_metadata);
    $tokens += array($token => '');
    $this->assertIdentical($tokens[$token], $expected, t("Token value for [@type:@token] was '@actual', expected value '@expected'.", array('@type' => $type, '@token' => $token, '@actual' => $tokens[$token], '@expected' => $expected)));
  }

  public function saveAlias($source, $alias, $langcode = Language::LANGCODE_NOT_SPECIFIED) {
    \Drupal::service('path.alias_storage')->delete(array('source' => $source, 'language', 'langcode' => $langcode));
    return \Drupal::service('path.alias_storage')->save($source, $alias, $langcode);
  }

  public function saveEntityAlias(EntityInterface $entity, $alias, $langcode = NULL) {
    // By default, use the entity language.
    if (!$langcode) {
      $langcode = $entity->language()->getId();
    }
    return $this->saveAlias('/' . $entity->urlInfo()->getInternalPath(), $alias, $langcode);
  }

  public function assertEntityAlias(EntityInterface $entity, $expected_alias, $langcode = NULL) {
    // By default, use the entity language.
    if (!$langcode) {
      $langcode = $entity->language()->getId();
    }
    $this->assertAlias('/' . $entity->urlInfo()->getInternalPath(), $expected_alias, $langcode);
  }

  public function assertEntityAliasExists(EntityInterface $entity) {
    return $this->assertAliasExists(array('source' => '/' . $entity->urlInfo()->getInternalPath()));
  }

  public function assertNoEntityAlias(EntityInterface $entity, $langcode = NULL) {
    // By default, use the entity language.
    if (!$langcode) {
      $langcode = $entity->language()->getId();
    }
    $this->assertEntityAlias($entity, '/' . $entity->urlInfo()->getInternalPath(), $langcode);
  }

  public function assertNoEntityAliasExists(EntityInterface $entity, $alias = NULL) {
    $path = array('source' => '/' . $entity->urlInfo()->getInternalPath());
    if (!empty($alias)) {
      $path['alias'] = $alias;
    }
    $this->assertNoAliasExists($path);
  }

  public function assertAlias($source, $expected_alias, $langcode = Language::LANGCODE_NOT_SPECIFIED) {
    \Drupal::service('path.alias_manager')->cacheClear($source);
    $this->assertEqual($expected_alias, \Drupal::service('path.alias_manager')->getAliasByPath($source, $langcode), t("Alias for %source with language '@language' is correct.",
      array('%source' => $source, '@language' => $langcode)));
  }

  public function assertAliasExists($conditions) {
    $path = \Drupal::service('path.alias_storage')->load($conditions);
    $this->assertTrue($path, t('Alias with conditions @conditions found.', array('@conditions' => var_export($conditions, TRUE))));
    return $path;
  }

  public function assertNoAliasExists($conditions) {
    $alias = \Drupal::service('path.alias_storage')->load($conditions);
    $this->assertFalse($alias, t('Alias with conditions @conditions not found.', array('@conditions' => var_export($conditions, TRUE))));
  }

  public function deleteAllAliases() {
    db_delete('url_alias')->execute();
    \Drupal::service('path.alias_manager')->cacheClear();
  }

  /**
   * @param array $values
   * @return \Drupal\taxonomy\VocabularyInterface
   */
  public function addVocabulary(array $values = array()) {
    $name = Unicode::strtolower($this->randomMachineName(5));
    $values += array(
      'name' => $name,
      'vid' => $name,
    );
    $vocabulary = entity_create('taxonomy_vocabulary', $values);
    $vocabulary->save();

    return $vocabulary;
  }

  public function addTerm(VocabularyInterface $vocabulary, array $values = array()) {
    $values += array(
      'name' => Unicode::strtolower($this->randomMachineName(5)),
      'vid' => $vocabulary->id(),
    );

    $term = entity_create('taxonomy_term', $values);
    $term->save();
    return $term;
  }

  public function assertEntityPattern($entity_type, $bundle, $langcode = Language::LANGCODE_NOT_SPECIFIED, $expected) {

    $values = [
      'langcode' => $langcode,
      \Drupal::entityTypeManager()->getDefinition($entity_type)->getKey('bundle') => $bundle,
    ];
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->create($values);

    $pattern = \Drupal::service('pathauto.generator')->getPatternByEntity($entity);
    $this->assertIdentical($expected, $pattern->getPattern());
  }

  public function drupalGetTermByName($name, $reset = FALSE) {
    if ($reset) {
      // @todo - implement cache reset.
    }
    $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(array('name' => $name));
    return !empty($terms) ? reset($terms) : FALSE;
  }
}
