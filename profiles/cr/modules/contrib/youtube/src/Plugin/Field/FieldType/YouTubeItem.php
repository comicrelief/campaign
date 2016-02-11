<?php

/**
 * @file
 * Contains \Drupal\youtube\Plugin\Field\FieldType\YouTubeItem.
 */

namespace Drupal\youtube\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;


/**
 * Plugin implementation of the 'youtube' field type.
 *
 * @FieldType(
 *   id = "youtube",
 *   label = @Translation("YouTube video"),
 *   description = @Translation("This field stores a YouTube video in the database."),
 *   default_widget = "youtube",
 *   default_formatter = "youtube_video"
 * )
 */
class YouTubeItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'input' => array(
          'description' => 'Video URL.',
          'type' => 'varchar',
          'length' => 1024,
          'not null' => FALSE,
        ),
        'video_id' => array(
          'description' => 'Video ID.',
          'type' => 'varchar',
          'length' => 20,
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['input'] = DataDefinition::create('string')
      ->setLabel(t('Video url'));

    $properties['video_id'] = DataDefinition::create('string')
      ->setLabel(t('Video id'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('input')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'input';
  }
}
