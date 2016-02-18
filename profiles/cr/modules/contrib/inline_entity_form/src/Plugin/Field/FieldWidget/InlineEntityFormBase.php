<?php

/**
 * @file
 * Contains \Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormBase.
 */

namespace Drupal\inline_entity_form\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Inline entity form widget base class.
 */
abstract class InlineEntityFormBase extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The inline entity form id.
   *
   * @var string
   */
  protected $iefId;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The inline entity from handler.
   *
   * @var \Drupal\inline_entity_form\InlineFormInterface
   */
  protected $iefHandler;

  /**
   * Constructs an InlineEntityFormBase object.
   *
   * @param array $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   Entity manager service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityManagerInterface $entity_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityManager = $entity_manager;

    $this->initializeIefController();
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
      $configuration['third_party_settings'],
      $container->get('entity.manager')
    );
  }

  /**
   * Initializes IEF form handler.
   */
  protected function initializeIefController() {
    if (!isset($this->iefHandler)) {
      $target_type = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');
      $this->iefHandler = $this->entityManager->getHandler($target_type, 'inline_form');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $keys = array_diff(parent::__sleep(), array('iefHandler'));
    return $keys;
  }

  /**
   * {@inheritdoc}
   */
  public function __wakeup() {
    parent::__wakeup();
    $this->initializeIefController();
  }

  /**
   * Sets inline entity form ID.
   *
   * @param string $ief_id
   *   The inline entity form ID.
   */
  protected function setIefId($ief_id) {
    $this->iefId = $ief_id;
  }

  /**
   * Gets inline entity form ID.
   *
   * @return string
   *   Inline entity form ID.
   */
  protected function getIefId() {
    return $this->iefId;
  }

  /**
   * Gets the target bundles for the current field.
   *
   * @return string[]
   *   A list of bundles.
   */
  protected function getTargetBundles() {
    $settings = $this->getFieldSettings();
    if (!empty($settings['handler_settings']['target_bundles'])) {
      $target_bundles = array_values($settings['handler_settings']['target_bundles']);
    }
    else {
      // If no target bundles have been specified then all are available.
      $target_bundles = array_keys($this->entityManager->getBundleInfo($settings['target_type']));
    }

    return $target_bundles;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'override_labels' => FALSE,
      'label_singular' => '',
      'label_plural' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $states_prefix = 'fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings]';
    $element = [];
    $element['override_labels'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override labels'),
      '#default_value' => $this->getSetting('override_labels'),
    ];
    $element['label_singular'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Singular label'),
      '#default_value' => $this->getSetting('label_singular'),
      '#states' => [
        'visible' => [
          ':input[name="' . $states_prefix . '[override_labels]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $element['label_plural'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Plural label'),
      '#default_value' => $this->getSetting('label_plural'),
      '#states' => array(
        'visible' => array(
          ':input[name="' . $states_prefix . '[override_labels]"]' => ['checked' => TRUE],
        ),
      ),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getSetting('override_labels')) {
      $summary[] = $this->t(
        'Overriden labels are used: %singular and %plural',
        ['%singular' => $this->getSetting('label_singular'), '%plural' => $this->getSetting('label_plural')]
      );
    }
    else {
      $summary[] = $this->t('Default labels are used.');
    }

    return $summary;
  }

  /**
   * Returns an array of entity type labels to be included in the UI text.
   *
   * @return array
   *   Associative array with the following keys:
   *   - 'singular': The label for singular form.
   *   - 'plural': The label for plural form.
   */
  protected function labels() {
    // The admin has specified the exact labels that should be used.
    if ($this->getSetting('override_labels')) {
      return [
        'singular' => $this->getSetting('label_singular'),
        'plural' => $this->getSetting('label_plural'),
      ];
    }
    else {
      $this->initializeIefController();
      return $this->iefHandler->labels();
    }
  }

  /**
   * Checks whether we can build entity form at all.
   *
   * - Is IEF handler loaded?
   * - Are we on a "real" entity form and not on default value widget?
   *
   * @param FormStateInterface $form_state
   *   Form state.
   *
   * @return bool
   *   TRUE if we are able to proceed with form build and FALSE if not.
   */
  protected function canBuildForm(FormStateInterface $form_state) {
    if ($this->isDefaultValueWidget($form_state)) {
      return FALSE;
    }

    if (!$this->iefHandler) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Gets inline entity form element.
   *
   * @param $operation
   *   Operation (i.e. 'add' or 'edit').
   * @param $language
   *   Entity langcode.
   * @param array $parents
   *   Array of parent element names.
   * @param EntityInterface $entity
   *   Optional entity object.
   * @param bool $save_entity
   *   IEF will attempt to save entity after attaching all field values if set to
   *   TRUE. Defaults to FALSE.
   *
   * @return array
   *   IEF form element structure.
   */
  protected function getInlineEntityForm($operation, $language, $delta, array $parents, $bundle = NULL, EntityInterface $entity = NULL, $save_entity = FALSE) {
    $ief = [
      '#type' => 'inline_entity_form',
      '#op' => $operation,
      '#save_entity' => $save_entity,
      '#ief_row_delta' => $delta,
      // Used by Field API and controller methods to find the relevant
      // values in $form_state.
      '#parents' => $parents,
      '#entity_type' => $this->getFieldSetting('target_type'),
      // Pass the langcode of the parent entity,
      '#language' => $language,
      // Labels could be overridden in field widget settings. We won't have
      // access to those in static callbacks (#process, ...) so let's add
      // them here.
      '#ief_labels' => $this->labels(),
      // Identifies the IEF widget to which the form belongs.
      '#ief_id' => $this->getIefId(),
      // Add the pre_render callback that powers the #fieldset form element key,
      // which moves the element to the specified fieldset without modifying its
      // position in $form_state->get('values').
      '#pre_render' => [[get_class($this), 'addFieldsetMarkup']],
    ];

    if ($entity) {
      // Store the entity on the form, later modified in the controller.
      $ief['#entity'] = $entity;
    }

    if ($bundle) {
      $ief['#bundle'] = $bundle;
    }

    return $ief;
  }

  /**
   * Adds submit callbacks to the inline entity form.
   *
   * @param array $element
   *   Form array structure.
   */
  public static function addIefSubmitCallbacks($element) {
    $element['#ief_element_submit'][] = [get_called_class(), 'submitSaveEntity'];
    return $element;
  }

  /**
   * Pre-render callback: Move form elements into fieldsets for presentation purposes.
   *
   * Inline forms use #tree = TRUE to keep their values in a hierarchy for
   * easier storage. Moving the form elements into fieldsets during form building
   * would break up that hierarchy, so it's not an option for Field API fields.
   * Therefore, we wait until the pre_render stage, where any changes we make
   * affect presentation only and aren't reflected in $form_state->getValues().
   */
  public static function addFieldsetMarkup($form) {
    $sort = [];
    foreach (Element::children($form) as $key) {
      $element = $form[$key];
      // In our form builder functions, we added an arbitrary #fieldset property
      // to any element that belongs in a fieldset. If this form element has that
      // property, move it into its fieldset.
      if (isset($element['#fieldset']) && isset($form[$element['#fieldset']])) {
        $form[$element['#fieldset']][$key] = $element;
        // Remove the original element this duplicates.
        unset($form[$key]);
        // Mark the fieldset for sorting.
        if (!in_array($key, $sort)) {
          $sort[] = $element['#fieldset'];
        }
      }
    }

    // Sort all fieldsets, so that element #weight stays respected.
    foreach ($sort as $key) {
      uasort($form[$key], 'element_sort');
    }

    return $form;
  }

  /**
   * Marks created/edited entity with "needs save" flag.
   *
   * Note that at this point the entity is not yet saved, since the user might
   * still decide to cancel the parent form.
   *
   * @param $entity_form
   *  The form of the entity being managed inline.
   * @param $form_state
   *   The form state of the parent form.
   */
  public static function submitSaveEntity($entity_form, FormStateInterface $form_state) {
    $ief_id = $entity_form['#ief_id'];
    $entity = $entity_form['#entity'];

    if ($entity_form['#op'] == 'add') {
      // Determine the correct weight of the new element.
      $weight = 0;
      $entities = $form_state->get(['inline_entity_form', $ief_id, 'entities']);
      if (!empty($entities)) {
        $weight = max(array_keys($entities)) + 1;
      }
      // Add the entity to form state, mark it for saving, and close the form.
      $entities[] = array(
        'entity' => $entity,
        '_weight' => $weight,
        'form' => NULL,
        'needs_save' => TRUE,
      );
      $form_state->set(['inline_entity_form', $ief_id, 'entities'], $entities);
    }
    else {
      $delta = $entity_form['#ief_row_delta'];
      $entities = $form_state->get(['inline_entity_form', $ief_id, 'entities']);
      $entities[$delta]['entity'] = $entity;
      $entities[$delta]['needs_save'] = TRUE;
      $form_state->set(['inline_entity_form', $ief_id, 'entities'], $entities);
    }
  }

  /**
   * Checks if current submit is relevant for IEF.
   *
   * We need to save all referenced entities and extract their IDs into field
   * values.
   *
   * @param array $form
   *   Complete form.
   * @param FormStateInterface $form_state
   *   Form state.
   */
  protected function isSubmitRelevant(array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $parents = array_merge($form['#parents'], [$field_name, 'form']);

    $trigger = $form_state->getTriggeringElement();
    if (isset($trigger['#limit_validation_errors']) && $trigger['#limit_validation_errors'] !== FALSE) {
      $imploded_parents = implode('', array_slice($parents, 0, -1));
      $relevant_sections = array_filter(
        $trigger['#limit_validation_errors'],
        function ($item) use ($imploded_parents) {
          $imploded_item = implode('', $item);
          return strpos($imploded_item, $imploded_parents) !== 0;
        }
      );

      if (empty($relevant_sections)) {
        return FALSE;
      }
    }

    return TRUE;
  }

}
