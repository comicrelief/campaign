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
use Drupal\taxonomy\VocabularyInterface;

/**
 * Helper test class with some added functions for testing.
 */
trait PathautoTestHelperTrait {

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

  public function assertNoEntityAliasExists(EntityInterface $entity) {
    $this->assertNoAliasExists(array('source' => '/' . $entity->urlInfo()->getInternalPath()));
  }

  public function assertAlias($source, $expected_alias, $langcode = Language::LANGCODE_NOT_SPECIFIED) {
    $alias = array('alias' => $source);
    foreach (db_select('url_alias')->fields('url_alias')->condition('source', $source)->execute() as $row) {
      $alias = (array) $row;
      if ($row->alias == $expected_alias) {
        break;
      }
    }
    $this->assertIdentical($alias['alias'], $expected_alias, t("Alias for %source with language '@language' was %actual, expected %expected.",
      array('%source' => $source, '%actual' => $alias['alias'], '%expected' => $expected_alias, '@language' => $langcode)));
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
    \Drupal::service('pathauto.manager')->resetCaches();
    $pattern = \Drupal::service('pathauto.manager')->getPatternByEntity($entity_type, $bundle, $langcode);
    $this->assertIdentical($expected, $pattern);
  }

  public function drupalGetTermByName($name, $reset = FALSE) {
    if ($reset) {
      // @todo - implement cache reset.
    }
    $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties(array('name' => $name));
    return !empty($terms) ? reset($terms) : FALSE;
  }
}
