<?php

namespace Drupal\yamlform_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormElementManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a abstract element type form for a form element.
 */
abstract class YamlFormUiElementTypeFormBase extends FormBase {

  /**
   * The form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * Constructs a YamlFormUiElementSelectTypeForm object.
   *
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $element_manager
   *   The form element manager.
   */
  public function __construct(YamlFormElementManagerInterface $element_manager) {
    $this->elementManager = $element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.yamlform.element')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return parent::submitForm($form, $form_state);
  }

  /**
   * Gets the sorted definition of all YamlFormElement plugins.
   *
   * @return array
   *   An array of YamlFormElement plugin definitions. Keys are element types.
   */
  protected function getDefinitions() {
    $definitions = $this->elementManager->getDefinitions();
    $definitions = $this->elementManager->getSortedDefinitions($definitions);
    $grouped_definitions = $this->elementManager->getGroupedDefinitions($definitions);

    // Get definitions with basic and advanced first and uncategorized elements
    // last.
    $no_category = '';
    $basic_category = (string) $this->t('Basic elements');
    $advanced_category = (string) $this->t('Advanced elements');
    $uncategorized = $grouped_definitions[$no_category];

    $sorted_definitions = [];
    $sorted_definitions += $grouped_definitions[$basic_category];
    $sorted_definitions += $grouped_definitions[$advanced_category];
    unset($grouped_definitions[$basic_category], $grouped_definitions[$advanced_category], $grouped_definitions[$no_category]);
    foreach ($grouped_definitions as $grouped_definition) {
      $sorted_definitions += $grouped_definition;
    }
    $sorted_definitions += $uncategorized;

    foreach ($sorted_definitions as &$plugin_definition) {
      if (!isset($plugin_definition['category'])) {
        $plugin_definition['category'] = $this->t('Other elements');
      }
    }

    return $sorted_definitions;
  }

}
