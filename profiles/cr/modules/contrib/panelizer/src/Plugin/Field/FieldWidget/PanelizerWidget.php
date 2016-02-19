<?php

/**
 * @file
 * Contains \Drupal\panelizer\Plugin\Field\FieldWidget\PanelizerWidget.
 */

namespace Drupal\panelizer\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'panelizer' widget.
 *
 * @FieldWidget(
 *   id = "panelizer",
 *   label = @Translation("Panelizer"),
 *   multiple_values = TRUE,
 *   field_types = {
 *     "panelizer"
 *   }
 * )
 */
class PanelizerWidget extends WidgetBase {

  /**
   * @return \Drupal\panels\PanelsDisplayManagerInterface
   */
  public function getPanelsManager() {
    // @todo: is it possible to inject this?
    return \Drupal::service('panels.display_manager');
  }

  /**
   * @return \Drupal\panelizer\Plugin\PanelizerEntityManager
   */
  public function getPanelizerManager() {
    // @todo: is it possible to inject this?
    return \Drupal::service('plugin.manager.panelizer_entity');
  }

  /**
   * @return \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  public function getEntityDisplayRepository() {
    // @todo: is it possible to inject this?
    return \Drupal::service('entity_display.repository');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'allow_panel_choice' => FALSE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    /*
    $elements['allow_panel_choice'] = array(
      '#type' => 'checkbox',
      '#title' => t('Allow panel choice'),
      '#default_value' => $this->getSetting('allow_panel_choice'),
    );
    */

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    if (!empty($this->getSetting('allow_panel_choice'))) {
      $summary[] = t('Allow panel choice');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $entity = $items->getEntity();
    $entity_type_id = $entity->getEntityTypeId();
    $entity_view_modes = $this->getEntityDisplayRepository()->getViewModes($entity_type_id);

    // Get the current values from the entity.
    $values = [];
    /** @var \Drupal\Core\Field\FieldItemInterface $item */
    foreach ($items as $item) {
      $values[$item->view_mode] = [
        'default' => $item->default,
        'panels_display' => $item->panels_display,
      ];
    }

    /** @var \Drupal\panelizer\Plugin\PanelizerEntityInterface $panelizer_plugin */
    $panelizer_plugin = $this->getPanelizerManager()->createInstance($entity_type_id, []);

    // If any view modes are missing, then set the default.
    foreach ($entity_view_modes as $view_mode => $view_mode_info) {
      if (!isset($values[$view_mode])) {
        $display = EntityViewDisplay::collectRenderDisplay($entity, $view_mode);
        if ($display->getThirdPartySetting('panelizer', 'enable', FALSE)) {
          $panels_display = $panelizer_plugin->getDefaultDisplay($display, $entity->bundle(), $view_mode);

          $values[$view_mode] = [
            'default' => 'default',
            'panels_display' => $this->getPanelsManager()->exportDisplay($panels_display),
          ];
        }
      }
    }

    // Add elements to the form for each view mode.
    $delta = 0;
    foreach ($values as $view_mode => $value) {
      $element[$delta]['view_mode'] = [
        '#type' => 'value',
        '#value' => $view_mode,
      ];

      if (!empty($this->getSetting('allow_panel_choice'))) {
        $element[$delta]['default'] = [
          '#type' => 'select',
          // @todo: list the view mode in the title
          // @todo: get the list of defaults
          '#options' => [],
          '#default_value' => $value['default'],
        ];
      }
      else {
        $element[$delta]['default'] = [
          '#type' => 'value',
          '#value' => $value['default'],
        ];
      }

      $element[$delta]['panels_display'] = [
        '#type' => 'value',
        '#value' => $value['panels_display'],
      ];

      $delta++;
    }

    return $element;
  }

}
