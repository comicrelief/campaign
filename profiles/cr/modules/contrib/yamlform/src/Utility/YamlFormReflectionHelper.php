<?php

namespace Drupal\yamlform\Utility;

/**
 * Helper class for reflection methods.
 */
class YamlFormReflectionHelper {

  /**
   * Get this element's class hierarchy.
   *
   * @return array
   *   An array containing this elements class hierarchy.
   */
  static public function getParentClasses($object, $base_class_name = '') {
    $class = get_class($object);
    $parent_classes = [
      self::getClassName($class),
    ];
    do {
      $parent_class = get_parent_class($class);
      $parent_class_name = self::getClassName($parent_class);
      $parent_classes[] = $parent_class_name;
      $class = $parent_class;
    } while ($parent_class_name != $base_class_name && $class);
    return array_reverse($parent_classes);
  }

  /**
   * Get a class's name without its namespace.
   *
   * @param string $class
   *   A class.
   *
   * @return string
   *   The class's name without its namespace.
   */
  static protected function getClassName($class) {
    $parts = preg_split('#\\\\#', $class);
    return end($parts);
  }

}
