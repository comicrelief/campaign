<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Provides an 'entity_reference' trait.
 */
trait YamlFormEntityReferenceTrait {

  /**
   * {@inheritdoc}
   */
  public function getRelatedTypes(array $element) {
    $types = [];
    $plugin_id = $this->getPluginId();
    $elements = $this->elementManager->getInstances();
    foreach ($elements as $element_name => $element_instance) {
      // Skip self.
      if ($plugin_id == $element_instance->getPluginId()) {
        continue;
      }
      if ($element_instance instanceof YamlFormEntityReferenceInterface) {
        $types[$element_name] = $element_instance->getPluginLabel();
      }
    }
    asort($types);
    return $types;
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    if (empty($value)) {
      return '';
    }

    $format = $this->getFormat($element);
    switch ($format) {
      case 'raw':
      case 'id':
      case 'label':
      case 'text':
        $items = $this->formatItems($element, $value, $options);
        if ($this->isMultiline($element)) {
          return [
            '#theme' => 'item_list',
            '#items' => $items,
          ];
        }
        else {
          return implode('; ', $items);
        }

      case 'link':
        return $this->formatLinks($element, $value, $options);

      default:
        return $this->formatView($element, $value, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    if (empty($value)) {
      return '';
    }

    $items = $this->formatItems($element, $value, $options);
    // Add dash (aka bullet) before each item.
    if ($this->isMultiline($element)) {
      foreach ($items as &$item) {
        $item = '- ' . $item;
      }
    }

    return implode("\n", $items);
  }

  /**
   * {@inheritdoc}
   */
  public function isMultiline(array $element) {
    if ($this->hasMultipleValues($element)) {
      return TRUE;
    }
    else {
      return parent::isMultiline($element);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'link';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'link' => $this->t('Link'),
      'id' => $this->t('Entity ID'),
      'label' => $this->t('Label'),
      'text' => $this->t('Label (ID)'),
      'teaser' => $this->t('Teaser'),
      'default' => $this->t('Default'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getExportDefaultOptions() {
    return [
      'entity_reference_format' => 'link',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportOptionsForm(array &$form, FormStateInterface $form_state, array $default_values) {
    if (isset($form['entity_reference'])) {
      return;
    }

    $form['entity_reference'] = [
      '#type' => 'details',
      '#title' => $this->t('Entity reference options'),
      '#open' => TRUE,
    ];
    $form['entity_reference']['entity_reference_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('Entity reference format'),
      '#options' => [
        'link' => $this->t('Entity link; with entity id, title and url in their own column.') . '<div class="description">' . $this->t("Entity links are suitable as long as there are not too many submissions (ie 1000's) pointing to just a few unique entities (ie 100's).") . '</div>',
        'id' => $this->t('Entity id; just the entity id column') . '<div class="description">' . $this->t('Entity links are suitable as long as there is mechanism for the referenced entity to be looked up external (ie REST API).') . '</div>',
      ],
      '#default_value' => $default_values['entity_reference_format'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportHeader(array $element, array $options) {
    if (!$this->hasMultipleValues($element) && $options['entity_reference_format'] == 'link') {
      if ($options['header_format'] == 'label') {
        $header = [
          (string) $this->t('ID'),
          (string) $this->t('Title'),
          (string) $this->t('URL'),
        ];
      }
      else {
        $header = ['id', 'title', 'url'];
      }
      return $this->prefixExportHeader($header, $element, $options);
    }
    else {
      return parent::buildExportHeader($element, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportRecord(array $element, $value, array $options) {
    if ($this->hasMultipleValues($element)) {
      $element = ['#format' => 'text'] + $element;
      $items = $this->formatItems($element, $value, $options);
      return [implode(', ', $items)];
    }

    if ($options['entity_reference_format'] == 'link') {
      $entity_type = $element['#target_type'];
      $entity_id = $value;

      $record = [];
      if ($entity_id && ($entity = entity_load($entity_type, $entity_id))) {
        $record[] = $entity->id();
        $record[] = $entity->label();
        $record[] = $entity->toUrl('canonical', ['absolute' => TRUE])->toString();
      }
      else {
        $record[] = "$entity_type:$entity_id";
        $record[] = '';
        $record[] = '';
      }
      return $record;
    }
    else {
      return parent::buildExportRecord($element, $value, $options);
    }
  }

  /**
   * Get target entity ids from entity autocomplete element's value.
   *
   * @param array|string|int $value
   *   Entity autocomplete element's value.
   *
   * @return array
   *   An array of entity ids.
   */
  protected function getTargetEntityIds($value) {
    if (is_array($value)) {
      return array_combine($value, $value);
    }
    else {
      return [$value => $value];
    }
  }

  /**
   * Format an entity autocomplete targets as array of strings.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options.
   *
   * @return array
   *   An entity autocomplete targets as array of strings
   *
   * @see \Drupal\yamlform\YamlFormSubmissionExporterInterface::formatRecordEntityAutocomplete
   */
  public function formatItems(array &$element, $value, array $options) {
    list($entity_ids, $entities) = $this->getTargetEntities($element, $value, $options);

    $format = $this->getFormat($element);

    $items = [];
    foreach ($entity_ids as $entity_id) {
      $entity = (isset($entities[$entity_id])) ? $entities[$entity_id] : NULL;
      switch ($format) {
        case 'id':
          $items[$entity_id] = $entity_id;
          break;

        case 'label':
          $items[$entity_id] = ($entity) ? $entity->label() : $entity_id;
          break;

        case 'raw':
          $entity_type = $element['#target_type'];
          $items[$entity_id] = "$entity_type:$entity_id";
          break;

        case 'text':
        default:
          if ($entity) {
            // Use `sprintf` instead of FormattableMarkup because we really just
            // want a basic string.
            $items[$entity_id] = sprintf('%s (%s)', $entity->label(), $entity->id());
          }
          else {
            $items[$entity_id] = $entity_id;
          }
          break;
      }
    }
    return $items;
  }

  /**
   * Format an entity autocomplete as a link or a list of links.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options.
   *
   * @return array|string
   *   A render array containing an entity autocomplete as a link or
   *   a list of links.
   */
  protected function formatLinks(array $element, $value, array $options) {
    list($entity_ids, $entities) = $this->getTargetEntities($element, $value, $options);

    $build = [];
    foreach ($entity_ids as $entity_id) {
      $entity = (isset($entities[$entity_id])) ? $entities[$entity_id] : NULL;
      if ($entity) {
        $build[$entity_id] = [
          '#type' => 'link',
          '#title' => $entity->label(),
          '#url' => $entity->toUrl()->setAbsolute(TRUE),
        ];
      }
      else {
        $build[$entity_id] = ['#markup' => $entity_id];
      }
    }

    if ($this->isMultiline($element) || count($build) > 1) {
      return [
        '#theme' => 'item_list',
        '#items' => $build,
      ];
    }
    else {
      return reset($build);
    }
  }

  /**
   * Format an entity autocomplete targets using a view mode.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options.
   *
   * @return array|string
   *   A render array containing an entity autocomplete targets using a view
   *   mode.
   */
  protected function formatView(array $element, $value, $options) {
    list($entity_ids, $entities) = $this->getTargetEntities($element, $value, $options);

    $view_mode = $this->getFormat($element);

    $build = [];
    foreach ($entity_ids as $entity_id) {
      $entity = (isset($entities[$entity_id])) ? $entities[$entity_id] : NULL;
      $build[$entity_id] = ($entity) ? entity_view($entity, $view_mode) : ['#markup' => $entity_id];
    }

    if ($this->isMultiline($element) || count($build) > 1) {
      return $build;
    }
    else {
      return reset($build);
    }
  }

  /**
   * Get referenced entities.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   * @param array $options
   *   An array of options.
   *
   * @return array|string
   *   A array containing $entity_ids and $entityies.
   */
  protected function getTargetEntities(array $element, $value, $options) {
    $langcode = (!empty($options['langcode'])) ? $options['langcode'] : \Drupal::languageManager()->getCurrentLanguage()->getId();

    $entity_ids = $this->getTargetEntityIds($value);
    $entities = entity_load_multiple($element['#target_type'], $entity_ids);
    foreach ($entities as $entity_id => $entity) {
      if ($entity->hasTranslation($langcode)) {
        $entities[$entity_id] = $entity->getTranslation($langcode);
      }
    }
    return [$entity_ids, $entities];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if ($properties = $form_state->getValue('properties')) {
      $target_type = $properties['target_type'];
      $selection_handler = $properties['selection_handler'];
      $selection_settings = $properties['selection_settings'];
    }
    else {
      // Set default #target_type and #selection_handler.
      if (empty($this->properties['target_type'])) {
        $this->properties['target_type'] = 'node';
      }
      if (empty($this->properties['selection_handler'])) {
        $this->properties['selection_handler'] = 'default:' . $this->properties['target_type'];
      }
      $target_type = $this->properties['target_type'];
      $selection_handler = $this->properties['selection_handler'];
      $selection_settings = $this->properties['selection_settings'];
    }

    /** @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerIn $entity_reference_selection_manager */
    $entity_reference_selection_manager = \Drupal::service('plugin.manager.entity_reference_selection');

    // @see \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem
    $selection_plugins = $entity_reference_selection_manager->getSelectionGroups($target_type);
    $handlers_options = [];
    foreach (array_keys($selection_plugins) as $selection_group_id) {
      if (array_key_exists($selection_group_id, $selection_plugins[$selection_group_id])) {
        $handlers_options[$selection_group_id] = Html::escape($selection_plugins[$selection_group_id][$selection_group_id]['label']);
      }
      elseif (array_key_exists($selection_group_id . ':' . $target_type, $selection_plugins[$selection_group_id])) {
        $selection_group_plugin = $selection_group_id . ':' . $target_type;
        $handlers_options[$selection_group_plugin] = Html::escape($selection_plugins[$selection_group_id][$selection_group_plugin]['base_plugin_label']);
      }
    }

    // ISSUE:
    // The AJAX handling for @EntityReferenceSelection plugins is just broken.
    //
    // WORKAROUND:
    // Implement custom #ajax that refresh the entire details element and
    // remove #ajax from selection settings to just get an MVP UI
    // for entity reference elements.
    //
    // @see https://www.drupal.org/project/issues/drupal?text=EntityReferenceSelection&version=8.x
    // @todo Figure out how to properly implement @EntityReferenceSelection plugins.
    $ajax_settings = [
      'callback' => [get_class($this), 'ajaxEntityReference'],
      'wrapper' => 'yamlform-entity-reference-selection-wrapper',
    ];
    $form['entity_reference'] = [
      '#type' => 'details',
      '#title' => t('Entity reference settings'),
      '#open' => TRUE,
      '#prefix' => '<div id="yamlform-entity-reference-selection-wrapper">',
      '#suffix' => '</div>',
    ];

    // Tags (only applies to 'entity_autocomplete' element).
    if ($this->hasProperty('tags')) {
      $form['entity_reference']['tags'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Tags'),
        '#description' => $this->t('Check this option if the user should be allowed to enter multiple entity references.'),
        '#return_value' => TRUE,
      ];
    }

    // Target type.
    $form['entity_reference']['target_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type of item to reference'),
      '#options' => \Drupal::entityManager()->getEntityTypeLabels(TRUE),
      '#required' => TRUE,
      '#empty_option' => t('- Select a target type -'),
      '#ajax' => $ajax_settings,
      '#default_value' => $target_type,
    ];
    // Selection handler.
    $form['entity_reference']['selection_handler'] = [
      '#type' => 'select',
      '#title' => $this->t('Reference method'),
      '#options' => $handlers_options,
      '#required' => TRUE,
      '#ajax' => $ajax_settings,
      '#default_value' => $selection_handler,
    ];
    // Selection settings.
    // Note: The below options are used to populate the #default_value for
    // selection settings.
    $entity_reference_selection_handler = $entity_reference_selection_manager->getInstance([
      'target_type' => $target_type,
      'handler' => $selection_handler,
      'handler_settings' => $selection_settings,
    ]);
    $form['entity_reference']['selection_settings'] = $entity_reference_selection_handler->buildConfigurationForm([], $form_state);
    $form['entity_reference']['selection_settings']['#tree'] = TRUE;

    $this->updateAjaxCallbackRecursive($form['entity_reference']['selection_settings'], $ajax_settings);

    if (isset($form['entity_reference']['selection_settings']['include_anonymous'])) {
      $form['entity_reference']['selection_settings']['include_anonymous']['#return_value'] = TRUE;
    }

    unset(
      // Remove auto create.
      $form['entity_reference']['selection_settings']['auto_create'],
      $form['entity_reference']['selection_settings']['auto_create_bundle'],
      // Remove the no-ajax submit button.
      $form['entity_reference']['selection_settings']['target_bundles_update']
    );

    // Disable AJAX callback that we don't need.
    unset($form['entity_reference']['selection_settings']['target_bundles']['#ajax']);
    unset($form['entity_reference']['selection_settings']['sort']['field']['#ajax']);

    // Remove user role filter, which is not working correctly.
    // @see \Drupal\user\Plugin\EntityReferenceSelection\UserSelection
    unset($form['entity_reference']['selection_settings']['filter']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    $values = $form_state->getValues();
    if (isset($values['selection_settings']['target_bundles']) && empty($values['selection_settings']['target_bundles'])) {
      unset($values['selection_settings']['target_bundles']);
    }
    if (isset($values['selection_settings']['sort']['field']) && $values['selection_settings']['sort']['field'] == '_none') {
      unset($values['selection_settings']['sort']);
    }
    if (isset($values['selection_settings']['include_anonymous']) && empty($values['selection_settings']['include_anonymous'])) {
      unset($values['selection_settings']['include_anonymous']);
    }
    $form_state->setValues($values);
  }

  /**
   * Replace #ajax = TRUE with a work #ajax callback.
   *
   * @param array $element
   *   A element.
   * @param array $ajax_settings
   *   A #ajax callback.
   */
  protected function updateAjaxCallbackRecursive(array &$element, array $ajax_settings) {
    foreach (Element::children($element) as $key) {
      $element[$key]['#access'] = TRUE;
      if (isset($element[$key]['#ajax']) && $element[$key]['#ajax'] === TRUE) {
        $element[$key]['#ajax'] = $ajax_settings;
      }
      $this->updateAjaxCallbackRecursive($element[$key], $ajax_settings);
    }
  }

  /**
   * AJAX callback for entity reference details element.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   An associative array containing entity reference details element.
   */
  public function ajaxEntityReference(array $form, FormStateInterface $form_state) {
    $element = $form['properties']['entity_reference'];
    return $element;
  }

}
