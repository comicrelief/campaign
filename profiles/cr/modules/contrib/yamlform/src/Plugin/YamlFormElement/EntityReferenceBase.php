<?php

/**
 * @file
 * Contains \Drupal\yamlform\Plugin\YamlFormElement\EntityReferenceBase.
 */

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a base 'entity_reference' element.
 */
abstract class EntityReferenceBase extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue(array &$element, $default_value) {
    if (isset($element['#default_value']) && (!empty($element['#default_value']) || $element['#default_value'] === 0)) {
      if (!empty($element['#tags'])) {
        $entity_ids = $this->getTargetEntityIds($element['#default_value']);
        $element['#default_value'] = ($entity_ids) ? entity_load_multiple($element['#target_type'], $entity_ids) : [];
      }
      else {
        $element['#default_value'] = entity_load($element['#target_type'], $element['#default_value']) ?: NULL;
      }
    }
    else {
      $element['#default_value'] = NULL;
    }
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
      case 'id':
      case 'label':
      case 'text':
        $items = $this->formatItems($element, $value);
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
        return $this->formatLinks($element, $value);

      default:
        return $this->formatView($element, $value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    if (empty($value)) {
      return '';
    }

    $items = $this->formatItems($element, $value);
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
    if (!empty($element['#tags'])) {
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
   * Format an entity autocomplete targets as array of strings.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   *
   * @return array
   *   An entity autocomplete targets as array of strings
   *
   * @see \Drupal\yamlform\YamlFormSubmissionExporter::formatRecordEntityAutocomplete
   */
  public function formatItems(array &$element, $value) {
    $entity_type = $element['#target_type'];
    $entity_ids = $this->getTargetEntityIds($value);
    $entities = ($entity_ids) ? entity_load_multiple($entity_type, $entity_ids) : [];

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
    if (empty($element['#tags']) && $options['entity_reference_format'] == 'link') {
      $header = [];
      if ($options['header_keys'] == 'label') {
        $label = $this->getLabel($element);
        $header[] = $label . ' ID';
        $header[] = $label . ' Title';
        $header[] = $label . ' URL';
      }
      else {
        $key = $this->getKey($element);
        $header[] = $key . '_id';
        $header[] = $key . '_title';
        $header[] = $key . '_url';
      }
      return $header;
    }
    else {
      return parent::buildExportHeader($element, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildExportRecord(array $element, $value, array $options) {
    if (!empty($element['#tags'])) {
      $element = ['#format' => 'text'] + $element;
      $items = $this->formatItems($element, $value);
      return [implode(', ', $items)];
    }

    $entity_type = $element['#target_type'];
    $entity_id = $value;

    if ($options['entity_reference_format'] == 'link') {
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
    $entity_ids = [];
    if (is_array($value)) {
      foreach ($value as $item) {
        $entity_ids[$item['target_id']] = $item['target_id'];
      }
    }
    else {
      $entity_ids = [$value => $value];
    }
    return $entity_ids;
  }

  /**
   * Format an entity autocomplete as a link or a list of links.
   *
   * @param array $element
   *   An element.
   * @param array|mixed $value
   *   A value.
   *
   * @return array|string
   *   A render array containing an entity autocomplete as a link or
   *   a list of links.
   */
  protected function formatLinks(array $element, $value) {
    $entity_ids = $this->getTargetEntityIds($value);
    $entities = entity_load_multiple($element['#target_type'], $entity_ids);

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
   *
   * @return array|string
   *   A render array containing an entity autocomplete targets using a view
   *   mode.
   */
  protected function formatView(array $element, $value) {
    $view_mode = $this->getFormat($element);
    $entity_ids = $this->getTargetEntityIds($value);
    $entities = entity_load_multiple($element['#target_type'], $entity_ids);

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

}
