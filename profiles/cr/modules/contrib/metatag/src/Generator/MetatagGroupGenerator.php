<?php
/**
 * @file
 * Contains \Drupal\metatag\Generator\MetatagGroupGenerator.
 */

namespace Drupal\metatag\Generator;

use Drupal\Console\Generator\Generator;

class MetatagGroupGenerator extends Generator {

  /**
   * Generator plugin.
   *
   * @param string $base_class
   * @param string $module
   * @param string $label
   * @param string $description
   * @param string $plugin_id
   * @param string $class_name
   * @param string $weight
   */
  public function generate($base_class, $module, $label, $description, $plugin_id, $class_name, $weight) {
    $parameters = [
      'base_class' => $base_class,
      'module' => $module,
      'label' => $label,
      'description' => $description,
      'plugin_id' => $plugin_id,
      'class_name' => $class_name,
      'weight' => $weight,
    ];

    $this->renderFile(
      'group.php.twig',
      $this->getSite()->getPluginPath($module, 'metatag/Group') . '/' . $class_name . '.php',
      $parameters
    );
  }

}
