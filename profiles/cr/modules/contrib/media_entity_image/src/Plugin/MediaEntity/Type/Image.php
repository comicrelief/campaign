<?php

namespace Drupal\media_entity_image\Plugin\MediaEntity\Type;

use Drupal\Core\Config\Config;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\MediaTypeBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides media type plugin for Image.
 *
 * @MediaType(
 *   id = "image",
 *   label = @Translation("Image"),
 *   description = @Translation("Provides business logic and metadata for local images.")
 * )
 */
class Image extends MediaTypeBase {

  /**
   * The image factory service..
   *
   * @var \Drupal\Core\Image\ImageFactory;
   */
  protected $imageFactory;

  /**
   * The exif data.
   *
   * @var array.
   */
  protected $exif;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   * @param \Drupal\Core\Config\Config $config
   *   Media entity config object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ImageFactory $image_factory, Config $config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_field_manager, $config);
    $this->imageFactory = $image_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('image.factory'),
      $container->get('config.factory')->get('media_entity.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function providedFields() {
    $fields = array(
      'mime' => t('File MIME'),
      'width' => t('Width'),
      'height' => t('Height'),
    );

    if (!empty($this->configuration['gather_exif'])) {
      $fields += array(
        'model' => t('Camera model'),
        'created' => t('Image creation datetime'),
        'iso' => t('Iso'),
        'exposure' => t('Exposure time'),
        'apperture' => t('Apperture value'),
        'focal_lenght' => t('Focal lenght'),
      );
    }
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getField(MediaInterface $media, $name) {
    $source_field = $this->configuration['source_field'];
    $property_name = $media->{$source_field}->first()->mainPropertyName();

    // Get the file, image and exif data.
    /** @var \Drupal\file\FileInterface $file */
    $file = $this->entityTypeManager->getStorage('file')->load($media->{$source_field}->first()->{$property_name});
    $image = $this->imageFactory->get($file->getFileUri());
    $uri = $file->getFileUri();

    // Return the field.
    switch ($name) {
      case 'mime':
        return !$file->filemime->isEmpty() ? $file->getMimeType() : FALSE;

      case 'width':
        $width = $image->getWidth();
        return $width ? $width : FALSE;

      case 'height':
        $height = $image->getHeight();
        return $height ? $height : FALSE;

      case 'size':
        $size = $file->getSize();
        return $size ? $size : FALSE;
    }

    if (!empty($this->configuration['gather_exif']) && function_exists('exif_read_data')) {
      switch ($name) {
        case 'model':
          return $this->getExifField($uri, 'Model');

        case 'created':
          $date = new DrupalDateTime($this->getExifField($uri, 'DateTimeOriginal'));
          return $date->getTimestamp();

        case 'iso':
          return $this->getExifField($uri, 'ISOSpeedRatings');

        case 'exposure':
          return $this->getExifField($uri, 'ExposureTime');

        case 'apperture':
          return $this->getExifField($uri, 'FNumber');

        case 'focal_lenght':
          return $this->getExifField($uri, 'FocalLength');
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $form_state->getFormObject()->getEntity();
    $options = [];
    $allowed_field_types = ['file', 'image'];
    foreach ($this->entityFieldManager->getFieldDefinitions('media', $bundle->id()) as $field_name => $field) {
      if (in_array($field->getType(), $allowed_field_types) && !$field->getFieldStorageDefinition()->isBaseField()) {
        $options[$field_name] = $field->getLabel();
      }
    }

    $form['source_field'] = [
      '#type' => 'select',
      '#title' => t('Field with source information'),
      '#description' => t('Field on media entity that stores Image file. You can create a bundle without selecting a value for this dropdown initially. This dropdown can be populated after adding fields to the bundle.'),
      '#default_value' => empty($this->configuration['source_field']) ? NULL : $this->configuration['source_field'],
      '#options' => $options,
    ];

    $form['gather_exif'] = [
      '#type' => 'select',
      '#title' => t('Whether to gather exif data.'),
      '#description' => t('Gather exif data using exif_read_data().'),
      '#default_value' => empty($this->configuration['gather_exif']) || !function_exists('exif_read_data') ? 0 : $this->configuration['gather_exif'],
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#disabled' => (function_exists('exif_read_data')) ? FALSE : TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultThumbnail() {
    return $this->config->get('icon_base') . '/image.png';
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnail(MediaInterface $media) {
    $source_field = $this->configuration['source_field'];

    /** @var \Drupal\file\FileInterface $file */
    if ($file = $this->entityTypeManager->getStorage('file')->load($media->{$source_field}->target_id)) {
      return $file->getFileUri();
    }

    return $this->getDefaultThumbnail();
  }

  /**
   * Get exif field value.
   *
   * @param string $uri
   *   The uri for the file that we are getting the Exif.
   * @param string $field
   *   The name of the exif field.
   *
   * @return string|bool
   *   The value for the requested field or FALSE if is not set.
   */
  protected function getExifField($uri, $field) {
    if (empty($this->exif)) {
      $this->exif = $this->getExif($uri);
    }
    return !empty($this->exif[$field]) ? $this->exif[$field] : FALSE;
  }

  /**
   * Read EXIF.
   *
   * @param string $uri
   *   The uri for the file that we are getting the Exif.
   *
   * @return array|bool
   *   An associative array where the array indexes are the header names and
   *   the array values are the values associated with those headers or FALSE
   *   if the data can't be read.
   */
  protected function getExif($uri) {
    return exif_read_data($uri, 'EXIF');
  }

}
