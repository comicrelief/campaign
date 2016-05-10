<?php

/**
 * @file
 * Contains \Drupal\view_mode\Plugin\Field\FieldType\ListViewModeItem.
 */

namespace Drupal\view_mode\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\OptionsProviderInterface;

/**
 * Plugin implementation of the 'list_view_mode' field type.
 *
 * @FieldType(
 *   id = "list_view_mode",
 *   label = @Translation("View mode"),
 *   description = @Translation("This field stores view modes"),
 *   default_widget = "view_mode_select_widget",
 *   default_formatter = "view_mode_default_formatter"
 * )
 */
class ListViewModeItem extends FieldItemBase implements OptionsProviderInterface {

  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'varchar',
          'length' => '255',
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('View mode'))
      ->addConstraint('Length', array('max' => 255))
      ->setRequired(TRUE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleOptions(AccountInterface $account = NULL) {
    $options = $this->getOptions();
    
    $view_modes = $this->getSetting('view_modes');

    $possible_options = array();
    foreach ($options as $entity_type => $data) {
      foreach ($data as $key => $value) {
        if (isset($view_modes[$key])) {
          $possible_options[$key] = $value;
          unset($options[$key]);
        }
      }
    }

    return $possible_options;    
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleValues(AccountInterface $account = NULL) {
    return array_keys($this->getPossibleOptions($account));
  }

  /**
   * {@inheritdoc}
   */
  public function getSettableValues(AccountInterface $account = NULL) {
    return $this->getPossibleValues($account);
  }

  /**
   * {@inheritdoc}
   */
  public function getSettableOptions(AccountInterface $account = NULL) {
    return $this->getPossibleOptions($account);
  }

  /**
   * Return view modes that can be selected. This depends on the entity this field is part of, currently no view modes from other entity types are selectable.
   * 
   * @return [type] [description]
   */
  protected function getOptions($entity_type = NULL) {
    $entity_manager = \Drupal::entityTypeManager();

    if ($entity_type) {
      $view_modes_info[$entity_type] = $entity_manager->getViewModes($entity_type);
    }
    else {
      $view_modes_info = $entity_manager->getAllViewModes();
    }

    $options = array();
    foreach ($view_modes_info as $type => $data) {
      foreach ($data as $view_mode_name => $view_mode_info) {
        $options[$type][$view_mode_name] = $view_mode_info['label'];
      }
    }

    if ($entity_type) {
      return $options[$entity_type];
    }
    else {
      return $options;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return array(
      'view_modes' => array(),
    ) + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = array();

    $options = $this->getOptions();
    foreach ($options as $entity_type => $data) {
      foreach ($data as $key => $value) {
        $flatten_options[$key] = $value;
      }
    }

    $element['view_modes'] = [
      '#type' => 'checkboxes',
      '#title' => t('Enabled view modes'),
      '#description' => t('Select the view modes that can be selected for this field.'),
      '#default_value' => $this->getSetting('view_modes'),
      '#options' => $flatten_options,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function fieldSettingsToConfigData(array $settings)
  {
    foreach ($settings['view_modes'] as $key => $status) {
      if (!$status) {
        unset($settings['view_modes'][$key]);
      }
    }
    return $settings;
  }
}
