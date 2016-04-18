<?php

/**
 * @file
 * Contains \Drupal\diff\DiffEntityParser.
 */

namespace Drupal\diff;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Language\LanguageInterface;

class DiffEntityParser {

  /**
   * The diff field builder plugin manager.
   *
   * @var \Drupal\diff\DiffBuilderManager
   */
  protected $diffBuilderManager;

  /**
   * Wrapper object for writing/reading simple configuration from diff.settings.yml
   */
  protected $config;

  /**
   * Wrapper object for writing/reading simple configuration from diff.plugins.yml
   */
  protected $pluginsConfig;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs an EntityComparisonBase object.
   *
   * @param DiffBuilderManager $diffBuilderManager
   *   The diff field builder plugin manager.
   * @param EntityManagerInterface $entityManager
   *   Entity Manager service.
   * @param ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(DiffBuilderManager $diffBuilderManager, EntityManagerInterface $entityManager, ConfigFactoryInterface $configFactory) {
    $this->entityManager = $entityManager;
    $this->config = $configFactory->get('diff.settings');
    $this->pluginsConfig =  $configFactory->get('diff.plugins');
    $this->diffBuilderManager = $diffBuilderManager;
  }

  /**
   * Transforms an entity into an array of strings.
   *
   * Parses an entity's fields and for every field it builds an array of string
   * to be compared. Basically this function transforms an entity into an array
   * of strings.
   *
   * @param ContentEntityInterface $entity
   *   An entity containing fields.
   *
   * @return array
   *   Array of strings resulted by parsing the entity.
   */
  public function parseEntity(ContentEntityInterface $entity) {
    $result = array();
    $langcode = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    // Load entity of current language, otherwise fields are always compared by
    // their default language.
    if ($entity->hasTranslation($langcode)) {
      $entity = $entity->getTranslation($langcode);
    }
    $entity_type_id = $entity->getEntityTypeId();
    // Load all entity base fields.
    $entity_base_fields = $this->entityManager->getBaseFieldDefinitions($entity_type_id);
    // Loop through entity fields and transform every FieldItemList object
    // into an array of strings according to field type specific settings.
    foreach ($entity as $field_items) {
      $field_type = $field_items->getFieldDefinition()->getType();
      $plugin_config = $this->pluginsConfig->get('field_types.' . $field_type);
      $plugin = NULL;
      if ($plugin_config && $plugin_config['type'] != 'hidden') {
        $plugin = $this->diffBuilderManager->createInstance($plugin_config['type'], $plugin_config['settings']);
      }
      if ($plugin) {
        // Configurable field. It is the responsibility of the class extending
        // this class to hide some configurable fields from comparison. This
        // class compares all configurable fields.
        if (!array_key_exists($field_items->getName(), $entity_base_fields)) {
          $build = $plugin->build($field_items);
          if (!empty($build)) {
            $result[$field_items->getName()] = $build;
          }
        }
        // If field is one of the entity base fields take visibility settings from
        // diff admin config page. This means that the visibility of these fields
        // is controlled per entity type.
        else {
          // Check if this field needs to be compared.
          $config_key = 'entity.' . $entity_type_id . '.' . $field_items->getName();
          $enabled = $this->config->get($config_key);
          if ($enabled) {
            $build = $plugin->build($field_items);
            if (!empty($build)) {
              $result[$field_items->getName()] = $build;
            }
          }
        }
      }
    }

    return $result;
  }
}
