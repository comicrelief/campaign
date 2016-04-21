<?php

namespace Drupal\hal\Normalizer;

use Drupal\Component\Utility\NestedArray;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Converts the Drupal field structure to HAL array structure.
 */
class FieldNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Field\FieldItemListInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($field, $format = NULL, array $context = array()) {
    $normalized_field_items = array();

    // Get the field definition.
    $entity = $field->getEntity();
    $field_name = $field->getName();
    $field_definition = $field->getFieldDefinition();

    // If this field is not translatable, it can simply be normalized without
    // separating it into different translations.
    if (!$field_definition->isTranslatable()) {
      $normalized_field_items = $this->normalizeFieldItems($field, $format, $context);
    }
    // Otherwise, the languages have to be extracted from the entity and passed
    // in to the field item normalizer in the context. The langcode is appended
    // to the field item values.
    else {
      foreach ($entity->getTranslationLanguages() as $language) {
        $context['langcode'] = $language->getId();
        $translation = $entity->getTranslation($language->getId());
        $translated_field = $translation->get($field_name);
        $normalized_field_items = array_merge($normalized_field_items, $this->normalizeFieldItems($translated_field, $format, $context));
      }
    }

    // Merge deep so that links set in entity reference normalizers are merged
    // into the links property.
    $normalized = NestedArray::mergeDeepArray($normalized_field_items);
    return $normalized;
  }


  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = array()) {
    if (!isset($context['target_instance'])) {
      throw new InvalidArgumentException('$context[\'target_instance\'] must be set to denormalize with the FieldNormalizer');
    }
    if ($context['target_instance']->getParent() == NULL) {
      throw new InvalidArgumentException('The field passed in via $context[\'target_instance\'] must have a parent set.');
    }

    $items = $context['target_instance'];
    $item_class = $items->getItemDefinition()->getClass();
    foreach ($data as $item_data) {
      // Create a new item and pass it as the target for the unserialization of
      // $item_data. Note: if $item_data is about a different language than the
      // default, FieldItemNormalizer::denormalize() will dismiss this item and
      // create a new one for the right language.
      $context['target_instance'] = $items->appendItem();
      $this->serializer->denormalize($item_data, $item_class, $format, $context);
    }

    return $items;

  }

  /**
   * Helper function to normalize field items.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field object.
   * @param string $format
   *   The format.
   * @param array $context
   *   The context array.
   *
   * @return array
   *   The array of normalized field items.
   */
  protected function normalizeFieldItems($field, $format, $context) {
    $normalized_field_items = array();
    if (!$field->isEmpty()) {
      foreach ($field as $field_item) {
        $normalized_field_items[] = $this->serializer->normalize($field_item, $format, $context);
      }
    }
    return $normalized_field_items;
  }

}
