<?php

namespace Drupal\yamlform\Element;

use Drupal\Core\Form\OptGroup;

/**
 * Trait for entity reference elements.
 */
trait YamlFormEntityTrait {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();
    $info['#target_type'] = NULL;
    $info['#selection_handler'] = 'default';
    $info['#selection_settings'] = [];
    return $info;
  }

  /**
   * Set referencable entities as options for an element.
   *
   * @param array $element
   *   An element.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the current user doesn't have access to the specified entity.
   *
   * @see \Drupal\system\Controller\EntityAutocompleteController
   */
  public static function setOptions(array &$element) {
    if (isset($element['#options'])) {
      return;
    }

    $selection_handler_options = [
      'target_type' => $element['#target_type'],
      'handler' => $element['#selection_handler'],
      'handler_settings' => $element['#selection_settings'],
    ];

    /** @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selection_manager */
    $selection_manager = \Drupal::service('plugin.manager.entity_reference_selection');
    $handler = $selection_manager->getInstance($selection_handler_options);
    $referenceable_entities = $handler->getReferenceableEntities();

    // Flatten all bundle grouping since they are not applicable to
    // YamlFormEntity elements.
    $options = [];
    foreach ($referenceable_entities as $bundle => $bundle_options) {
      $options += $bundle_options;
    }

    // Only select menu can support optgroups.
    if ($element['#type'] !== 'yamlform_entity_select') {
      $options = OptGroup::flattenOptions($options);
    }

    $element['#options'] = $options;
  }

}
