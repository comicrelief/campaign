<?php

/**
 * @file
 * Contains \Drupal\pathauto\Plugin\AliasType\EntityAliasTypeBase.
 */

namespace Drupal\pathauto\Plugin\pathauto\AliasType;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\pathauto\AliasTypeBatchUpdateInterface;
use Drupal\pathauto\AliasTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A pathauto alias type plugin for entities with canonical links.
 *
 * @AliasType(
 *   id = "canonical_entities",
 *   deriver = "\Drupal\pathauto\Plugin\Deriver\EntityAliasTypeDeriver"
 * )
 */
class EntityAliasTypeBase extends ContextAwarePluginBase implements AliasTypeInterface, AliasTypeBatchUpdateInterface, ContainerFactoryPluginInterface {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The path prefix for this entity type.
   *
   * @var string
   */
  protected $prefix;

  /**
   * Constructs a EntityAliasTypeBase instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    $definition = $this->getPluginDefinition();
    // Cast the admin label to a string since it is an object.
    // @see \Drupal\Core\StringTranslation\TranslationWrapper
    return (string) $definition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenTypes() {
    $definition = $this->getPluginDefinition();
    return $definition['types'];
  }

  /**
   * {@inheritdoc}
   */
  public function batchUpdate(&$context) {
    if (!isset($context['sandbox']['current'])) {
      $context['sandbox']['count'] = 0;
      $context['sandbox']['current'] = 0;
    }

    $entity_type = $this->entityTypeManager->getDefinition($this->getEntityTypeId());
    $id_key = $entity_type->getKey('id');

    $query = db_select($entity_type->get('base_table'), 'base_table');
    $query->leftJoin('url_alias', 'ua', "CONCAT('" . $this->getSourcePrefix() . "' , base_table.$id_key) = ua.source");
    $query->addField('base_table', $id_key, 'id');
    $query->isNull('ua.source');
    $query->condition('base_table.' . $id_key, $context['sandbox']['current'], '>');
    $query->orderBy('base_table.' . $id_key);
    $query->addTag('pathauto_bulk_update');
    $query->addMetaData('entity', $this->getEntityTypeId());

    // Get the total amount of items to process.
    if (!isset($context['sandbox']['total'])) {
      $context['sandbox']['total'] = $query->countQuery()->execute()->fetchField();

      // If there are no entities to update, then stop immediately.
      if (!$context['sandbox']['total']) {
        $context['finished'] = 1;
        return;
      }
    }

    $query->range(0, 25);
    $ids = $query->execute()->fetchCol();

    $this->bulkUpdate($ids);
    $context['sandbox']['count'] += count($ids);
    $context['sandbox']['current'] = max($ids);
    $context['message'] = t('Updated alias for %label @id.', array('%label' => $entity_type->getLabel(), '@id' => end($ids)));

    if ($context['sandbox']['count'] != $context['sandbox']['total']) {
      $context['finished'] = $context['sandbox']['count'] / $context['sandbox']['total'];
    }
  }

  /**
   * Returns the entity type ID.
   *
   * @return string
   *   The entity type ID.
   */
  protected function getEntityTypeId() {
    return $this->getDerivativeId();
  }

  /**
   * Update the URL aliases for multiple entities.
   *
   * @param array $ids
   *   An array of entity IDs IDs.
   * @param array $options
   *   An optional array of additional options.
   */
  protected function bulkUpdate(array $ids, array $options = array()) {
    $options += array('message' => FALSE);

    $entities = $this->entityTypeManager->getStorage($this->getEntityTypeId())->loadMultiple($ids);
    foreach ($entities as $entity) {
      // Update aliases for the entity's default language and its translations.
      foreach ($entity->getTranslationLanguages() as $langcode => $language) {
        $translated_entity = $entity->getTranslation($langcode);
        \Drupal::service('pathauto.generator')->updateEntityAlias($translated_entity, 'bulkupdate', $options);
      }
    }

    if (!empty($options['message'])) {
      drupal_set_message(\Drupal::translation()->formatPlural(count($ids), 'Updated 1 %label URL alias.', 'Updated @count %label URL aliases.'), array('%label' => $this->getLabel()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = [];
    $dependencies['module'][] = $this->entityTypeManager->getDefinition($this->getEntityTypeId())->getProvider();
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($object) {
    return $object instanceof FieldableEntityInterface && $object->getEntityTypeId() == $this->getEntityTypeId();
  }

  /**
   * {@inheritdoc}
   */
  public function getSourcePrefix() {
    if (empty($this->prefix)) {
      $entity_type = $this->entityTypeManager->getDefinition($this->getEntityTypeId());
      $path = $entity_type->getLinkTemplate('canonical');
      $this->prefix = substr($path, 0, strpos($path, '{'));
    }
    return $this->prefix;
  }

}
