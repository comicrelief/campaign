<?php

namespace Drupal\yamlform\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\yamlform\YamlFormMessageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Form rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "yamlform_entity_reference_entity_view",
 *   label = @Translation("Form"),
 *   description = @Translation("Display the referenced form with default submission data."),
 *   field_types = {
 *     "yamlform"
 *   }
 * )
 */
class YamlFormEntityReferenceEntityFormatter extends EntityReferenceFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The message manager.
   *
   * @var \Drupal\yamlform\YamlFormMessageManagerInterface
   */
  protected $messageManager;

  /**
   * YamlFormEntityReferenceEntityFormatter constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\yamlform\YamlFormMessageManagerInterface $message_manager
   *   The message manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, YamlFormMessageManagerInterface $message_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->messageManager = $message_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('yamlform.message_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $source_entity = $items->getEntity();
    $this->messageManager->setSourceEntity($source_entity);

    $elements = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      /** @var \Drupal\yamlform\YamlFormInterface $entity */

      // Do not display the form if the current user can't create submissions.
      if ($entity->id() && !$entity->access('submission_create')) {
        continue;
      }

      if ($entity->id() && $items[$delta]->status) {
        $elements[$delta] = $entity->getSubmissionForm();
      }
      else {
        $this->messageManager->setYamlForm($entity);
        $elements[$delta] = $this->messageManager->build(YamlFormMessageManagerInterface::FORM_CLOSED_MESSAGE);
      }
    }

    return $elements;
  }

}
