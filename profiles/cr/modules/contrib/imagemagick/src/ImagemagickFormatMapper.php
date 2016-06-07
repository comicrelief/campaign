<?php

namespace Drupal\imagemagick;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Schema\SchemaCheckTrait;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
// @todo change if extension mapping service gets in, see #2311679
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * Provides the ImageMagick format mapper.
 */
class ImagemagickFormatMapper implements ImagemagickFormatMapperInterface {

  use SchemaCheckTrait;
  use StringTranslationTrait;

  /**
   * The cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The MIME type guessing service.
   * @todo change if extension mapping service gets in, see #2311679
   *
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
   */
  protected $mimeTypeMapper;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The typed config service.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfig;

  /**
   * Constructs an ImagemagickFormatmapper object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_service
   *   The cache service.
   * @param \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface $mime_type_mapper
   *   The MIME type mapping service.
   *   @todo change if extension mapping service gets in, see #2311679
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config
   *   The typed config service.
   */
  public function __construct(CacheBackendInterface $cache_service, MimeTypeGuesserInterface $mime_type_mapper, ConfigFactoryInterface $config_factory, TypedConfigManagerInterface $typed_config) {
    $this->cache = $cache_service;
    // @todo change if extension mapping service gets in, see #2311679
    $this->mimeTypeMapper = $mime_type_mapper;
    $this->configFactory = $config_factory;
    $this->typedConfig = $typed_config;
  }

  /**
   * {@inheritdoc}
   */
  public function validateMap(array $map) {
    $errors = [];

    // Get current config object and change the format map.
    $data = $this->configFactory->get('imagemagick.settings')->get();
    $data['image_formats'] = $map;

    // Validates against schema.
    $schema_errors = $this->checkConfigSchema($this->typedConfig, 'imagemagick.settings', $data);
    if ($schema_errors !== TRUE) {
      foreach ($schema_errors as $key => $value) {
        list($object, $path) = explode(':', $key);
        $components = explode('.', $path);
        if ($components[0] === 'image_formats') {
          if (isset($components[2])) {
            $errors[$components[1]]['variables'][$components[2]][] = $value;
          }
          else {
            $errors[$components[1]]['format'][] = $value;
          }
        }
      }
    }

    // Other checks.
    foreach ($map as $key => $value) {
      if (Unicode::strtoupper($key) != $key) {
        // Formats must be typed in uppercase.
        $errors[$key]['format'][] = $this->t("The format (@key) must be entered in all uppercase characters.", ['@key' => $key])->render();
      }
      if (!isset($value['mime_type'])) {
        // Formats must have a MIME type mapped.
        $errors[$key]['format'][] = $this->t("Missing mime_type variable.")->render();
      }
      elseif (!in_array($value['mime_type'], $this->mimeTypeMapper->getMimeTypes())) {
        // MIME type must exist.
        $errors[$key]['variables']['mime_type'][] = $this->t("MIME type (@mime_type) not found.", ['@mime_type' => $value['mime_type']])->render();
      }
    }

    return $errors;
  }

  /**
   * {@inheritdoc}
   */
  public function isFormatEnabled($format) {
    $format = Unicode::strtoupper($format);
    return $format ? isset($this->resolveEnabledFormats()[$format]) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getMimeTypeFromFormat($format) {
    $format = Unicode::strtoupper($format);
    if ($this->isFormatEnabled($format)) {
      return $this->resolveEnabledFormats()[$format];
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormatFromExtension($extension) {
    $extension = Unicode::strtolower($extension);
    $enabled_extensions = $this->resolveEnabledExtensions();
    return $extension ? (isset($enabled_extensions[$extension]) ? $enabled_extensions[$extension] : NULL) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnabledFormats() {
    return array_keys($this->resolveEnabledFormats());
  }

  /**
   * {@inheritdoc}
   */
  public function getEnabledExtensions() {
    return array_keys($this->resolveEnabledExtensions());
  }

  /**
   * Returns the enabled image formats, processing the config map.
   *
   * Results are cached for subsequent access. Saving the config will
   * invalidate the cache.
   *
   * @return array
   *   An associative array with ImageMagick formats as keys and their MIME
   *   type as values.
   */
  protected function resolveEnabledFormats() {
    if ($cache = $this->cache->get("imagemagick:enabled_formats")) {
      $enabled_image_formats = $cache->data;
    }
    else {
      $config = $this->configFactory->get('imagemagick.settings');
      $image_formats = $config->get('image_formats');
      $enabled_image_formats = [];
      foreach ($image_formats as $format => $data) {
        if (!isset($data['enabled']) || (isset($data['enabled']) && $data['enabled'])) {
          if (isset($data['mime_type']) && in_array($data['mime_type'], $this->mimeTypeMapper->getMimeTypes())) {
            $enabled_image_formats[$format] = $data['mime_type'];
          }
        }
      }
      ksort($enabled_image_formats);
      $this->cache->set("imagemagick:enabled_formats", $enabled_image_formats, Cache::PERMANENT, $config->getCacheTags());
    }
    return $enabled_image_formats;
  }


  /**
   * Returns the enabled image file extensions, processing the config map.
   *
   * Results are cached for subsequent access. Saving the config will
   * invalidate the cache.
   *
   * @return array
   *   An associative array with file extensions as keys and their ImageMagick
   *   format as values.
   */
  protected function resolveEnabledExtensions() {
    if ($cache = $this->cache->get("imagemagick:enabled_extensions")) {
      $extensions = $cache->data;
    }
    else {
      // Get configured image formats.
      $image_formats = $this->configFactory->get('imagemagick.settings')->get('image_formats');

      // Get only enabled formats.
      $enabled_image_formats = array_keys($this->resolveEnabledFormats());

      // Apply defaults.
      foreach ($enabled_image_formats as $format) {
        if (isset($image_formats[$format]) && is_array($image_formats[$format])) {
          $image_formats[$format] += [
            'mime_type' => NULL,
            'weight' => 0,
            'exclude_extensions' => NULL,
          ];
        }
      }

      // Scans the enabled formats to determine enabled file extensions and
      // their mapping to the internal Image/GraphicsMagick format.
      $extensions = [];
      $excluded_extensions = [];
      foreach ($enabled_image_formats as $format) {
        $format_extensions = $this->mimeTypeMapper->getExtensionsForMimeType($image_formats[$format]['mime_type']);
        $weight_checked_extensions = [];
        foreach ($format_extensions as $ext) {
          if (!isset($extensions[$ext])) {
            $weight_checked_extensions[$ext] = $format;
          }
          else {
            // Extension is already present in the array, lower weight format
            // prevails.
            if ($image_formats[$format]['weight'] < $image_formats[$extensions[$ext]]['weight']) {
              $weight_checked_extensions[$ext] = $format;
            }
          }
        }
        $extensions = array_merge($extensions, $weight_checked_extensions);
        // Accumulate excluded extensions.
        if ($image_formats[$format]['exclude_extensions']) {
          $exclude_extensions_string = Unicode::strtolower(preg_replace('/\s+/', '', $image_formats[$format]['exclude_extensions']));
          $excluded_extensions = array_merge($excluded_extensions, array_intersect($format_extensions, explode(',', $exclude_extensions_string)));
        }
      }

      // Remove the excluded extensions.
      $excluded_extensions = array_unique($excluded_extensions);
      $excluded_extensions = array_combine($excluded_extensions, $excluded_extensions);
      $extensions = array_diff_key($extensions, $excluded_extensions);

      ksort($extensions);
      $this->cache->set("imagemagick:enabled_extensions", $extensions, Cache::PERMANENT, $this->configFactory->get('imagemagick.settings')->getCacheTags());
    }

    return $extensions;
  }

}
