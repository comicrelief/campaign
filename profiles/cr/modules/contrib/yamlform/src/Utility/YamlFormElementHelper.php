<?php

namespace Drupal\yamlform\Utility;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Template\Attribute;

/**
 * Helper class form element methods.
 */
class YamlFormElementHelper {

  /**
   * Ignored element properties.
   *
   * @var array
   */
  public static $ignoredProperties = [
    // Properties that will allow code injection.
    '#allowed_tags' => '#allowed_tags',
      // Properties that will break form data handling.
    '#tree' => '#tree',
    '#array_parents' => '#array_parents',
    '#parents' => '#parents',
    // Properties that will cause unpredictable rendering.
    '#weight' => '#weight',
    // Callbacks are blocked to prevent unwanted code executions.
    '#after_build' => '#after_build',
    '#element_validate' => '#element_validate',
    '#post_render' => '#post_render',
    '#pre_render' => '#pre_render',
    '#process' => '#process',
    '#submit' => '#submit',
    '#validate' => '#validate',
    '#value_callback' => '#value_callback',
  ];

  /**
   * Regular expression used to determine if sub-element property should be ignored.
   *
   * @var string
   */
  protected static $ignoredPropertiesRegExp;

  /**
   * Determine if a form element's title is displayed.
   *
   * @param array $element
   *   A form element.
   *
   * @return bool
   *   TRUE if a form element's title is displayed.
   */
  public static function isTitleDisplayed(array $element) {
    return (!empty($element['#title']) && (empty($element['#title_display']) || !in_array($element['#title_display'], ['invisible', ['attribute']]))) ? TRUE : FALSE;
  }

  /**
   * Replaces all tokens in a given render element with appropriate values.
   *
   * @param array $element
   *   A render element.
   * @param array $data
   *   (optional) An array of keyed objects.
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the token
   *   replacement process.
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) An object to which static::generate() and the hooks and
   *   functions that it invokes will add their required bubbleable metadata.
   *
   * @see \Drupal\Core\Utility\Token::replace()
   */
  public static function replaceTokens(array &$element, array $data = [], array $options = [], BubbleableMetadata $bubbleable_metadata = NULL) {
    foreach ($element as $element_property => &$element_value) {
      // Most strings won't contain tokens so lets check and return ASAP.
      if (is_string($element_value) && strpos($element_value, '[') !== FALSE) {
        $element[$element_property] = \Drupal::token()->replace($element_value, $data, $options);
      }
      elseif (is_array($element_value)) {
        self::replaceTokens($element_value, $data, $options, $bubbleable_metadata);
      }
    }
  }

  /**
   * Get an associative array containing a render element's properties.
   *
   * @param array $element
   *   A render element.
   *
   * @return array
   *   An associative array containing a render element's properties.
   */
  public static function getProperties(array $element) {
    $properties = [];
    foreach ($element as $key => $value) {
      if (Element::property($key)) {
        $properties[$key] = $value;
      }
    }
    return $properties;
  }

  /**
   * Remove all properties from a render element.
   *
   * @param array $element
   *   A render element.
   *
   * @return array
   *   A render element with no properties.
   */
  public static function removeProperties(array $element) {
    foreach ($element as $key => $value) {
      if (Element::property($key)) {
        unset($element[$key]);
      }
    }
    return $element;
  }

  /**
   * Add prefix to all top level keys in an associative array.
   *
   * @param array $array
   *   An associative array.
   * @param string $prefix
   *   Prefix to be prepended to all keys.
   *
   * @return array
   *   An associative array with all top level keys prefixed.
   */
  public static function addPrefix(array $array, $prefix = '#') {
    $prefixed_array = [];
    foreach ($array as $key => $value) {
      if ($key[0] != $prefix) {
        $key = $prefix . $key;
      }
      $prefixed_array[$key] = $value;
    }
    return $prefixed_array;
  }

  /**
   * Remove prefix from all top level keys in an associative array.
   *
   * @param array $array
   *   An associative array.
   * @param string $prefix
   *   Prefix to be remove from to all keys.
   *
   * @return array
   *   An associative array with prefix removed from all top level keys.
   */
  public static function removePrefix(array $array, $prefix = '#') {
    $unprefixed_array = [];
    foreach ($array as $key => $value) {
      if ($key[0] == $prefix) {
        $key = preg_replace('/^' . $prefix . '/', '', $key);
      }
      $unprefixed_array[$key] = $value;
    }
    return $unprefixed_array;
  }

  /**
   * Fix form element #states handling.
   *
   * @param array $element
   *   A form element that is missing the 'data-drupal-states' attribute.
   */
  public static function fixStatesWrapper(array &$element) {
    if (empty($element['#states'])) {
      return;
    }

    $attributes = [];
    $attributes['class'][] = 'js-form-wrapper';
    $attributes['data-drupal-states'] = Json::encode($element['#states']);

    $element += ['#prefix' => '', '#suffix' => ''];

    // ISSUE: JSON is being corrupted when the prefix is rendered.
    // $element['#prefix'] = '<div ' . new Attribute($attributes) . '>' . $element['#prefix'];
    // WORKAROUND: Safely set filtered #prefix to FormattableMarkup.
    $allowed_tags = isset($element['#allowed_tags']) ? $element['#allowed_tags'] : Xss::getHtmlTagList();
    $element['#prefix'] = new FormattableMarkup('<div' . new Attribute($attributes) . '>' . Xss::filter($element['#prefix'], $allowed_tags), []);
    $element['#suffix'] = $element['#suffix'] . '</div>';
  }

  /**
   * Get ignored properties from a form element.
   *
   * @param array $element
   *   A form element.
   *
   * @return array
   *   An array of ignored properties.
   */
  public static function getIgnoredProperties(array $element) {
    $ignored_properties = [];
    foreach ($element as $key => $value) {
      if (Element::property($key)) {
        if (self::isIgnoredProperty($key)) {
          $ignored_properties[$key] = $key;
        }
      }
      elseif (is_array($value)) {
        $ignored_properties += self::getIgnoredProperties($value, $ignored_properties);
      }
    }
    return $ignored_properties;
  }

  /**
   * Remove ignored properties from an element.
   *
   * @param array $element
   *   A form element.
   *
   * @return array
   *   A form element with ignored properties removed.
   */
  public static function removeIgnoredProperties(array $element) {
    foreach ($element as $key => $value) {
      if (Element::property($key) && self::isIgnoredProperty($key)) {
        unset($element[$key]);
      }
    }
    return $element;
  }

  /**
   * Determine if an element's property should be ignored.
   *
   * Subelement properties are delimited using __.
   *
   * @param string $property
   *   A property name.
   *
   * @return bool
   *   TRUE is the property should be ignored.
   *
   * @see \Drupal\yamlform\Element\YamlFormSelectOther
   * @see \Drupal\yamlform\Element\YamlFormCompositeBase::processYamlFormComposite
   */
  protected static function isIgnoredProperty($property) {
    // Build cached ignored properties regular expression.
    if (!isset(self::$ignoredPropertiesRegExp)) {
      self::$ignoredPropertiesRegExp = '/__(' . implode('|', array_keys(self::removePrefix(self::$ignoredProperties))) . ')$/';
    }

    if (isset(self::$ignoredProperties[$property])) {
      return TRUE;
    }
    elseif (strpos($property, '__') !== FALSE && preg_match(self::$ignoredPropertiesRegExp, $property)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
