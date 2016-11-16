<?php

/**
 * @file
 * Display Suite drush integration.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;

/**
 * Implements hook_drush_command().
 */
function ds_drush_command() {
  $items = array();

  $items['ds-build'] = array(
    'description' => 'Create a basic template and configuration file for a new Display Suite layout.',
    'arguments' => array(
      'name'    => 'Name for your layout.',
    ),
    'options' => array(
      'name'    => 'Name for your layout.',
      'regions' => 'Regions for your layout, comma-separated.',
      'css'     => 'Set this to true if you want to include a CSS file for your layout.',
      'image'   => 'Set this to true if you want to include a preview image for your layout.',
    ),
    'examples' => array(
      'drush ds-build "My layout name"' => 'Create a layout with a few example regions.',
      'drush ds-build "My layout name" --regions="Region 1, Region 2"' => 'Create a layout with custom regions.',
      'drush ds-build "My layout name" --css' => 'Create a layout with an included CSS file.',
    ),
  );

  $items['ds-field'] = array(
    'description' => 'Create a Display Suite plugin',
    'arguments' => array(
      'name'    => 'Name for your Display Suite field class',
    ),
    'options' => array(
      'name'    => 'Name for your class.',
      'entity'  => 'Name of the entity',
    ),
    'examples' => array(
      'drush ds-field "MyField"' => 'Create a field with only a render function',
      'drush ds-field "MyTest" --entity=user' => 'Create a field for the user entity',
    ),
  );

  return $items;
}

/**
 * Create a basic template and configuration file for a new Display Suite
 * layout.
 */
function drush_ds_build($name = NULL) {
  // Determine the layout name.
  if (!isset($name)) {
    $name = drush_get_option('name');
  }
  if (!$name) {
    drush_die(dt('You need to set a name for your layout. Type "drush help ds-build" for help.'));
  }

  // Determine the machine name.
  $machine_name = ds_prepare_machine_name($name);

  // Determine the path to our example layout templates.
  $ds_layout_path = dirname(__FILE__) . '/example_layout';

  // We create files in the current working directory.
  $layout_path = drush_cwd() . '/' . $machine_name;
  drush_op('mkdir', $layout_path);

  // Determine regions.
  $regions = drush_get_option('regions');
  if ($regions) {
    $regions = preg_split('/,(\ )?/', $regions);
  }

  // Copy the example templates.
  $twig_machine_name = strtr($machine_name, '_', '-');
  drush_op('copy', $ds_layout_path . '/example-layout.html.twig', $layout_path . "/$twig_machine_name.html.twig");
  drush_op('copy', $ds_layout_path . '/example_layout.inc', $layout_path . "/$machine_name.inc");

  // Prepare an array of things that need to be rewritten in our templates.
  $find = array();
  $replace = array();

  // Replace example name.
  $find[] = '/example layout/i';
  $replace[] = $name;
  $find[] = '/example_layout/';
  $replace[] = $machine_name;

  // Include a CSS file for this layout.
  $css = drush_get_option('css');
  if (isset($css)) {
    drush_op('copy', $ds_layout_path . '/example_layout.css', $layout_path . "/$machine_name.css");

    // Replace example styling if we have custom regions.
    if ($regions) {
      // Separate variables so this won't mess up our other templates.
      $css_find = $find;
      $css_replace = $replace;

      $css_find[] = "/(\*\/\n\n).+(\n)$/s";
      $css_replace[] = '$1' . ds_prepare_regions_css($regions) . '$2';

      drush_op('ds_file_preg_replace', array($layout_path . "/$machine_name.css"), $css_find, $css_replace);
    }

    // Uncomment the CSS rule in our configuration.
    $find[] = "/\/\/ ('css' => TRUE,)/";
    $replace[] = '$1';
  }

  // Check on form option.
  $image = drush_get_option('image');
  if (isset($image)) {
    // Uncomment the Form rule in our configuration.
    $find[] = "/\/\/ ('image' => TRUE,)/";
    $replace[] = '$1';
  }

  // Replace example region PHP/HTML code.
  if ($regions) {
    $find[] = '/  {# regions #}.+{# \/regions #}/s';
    $replace[] = ds_prepare_regions_html($regions);
    $find[] = "/( \* Regions:\n).+(\n \*\/)/s";
    $replace[] = '$1' . ds_prepare_regions_variable_documentation($regions) . '$2';
    $find[] = "/(    'regions' => array\(\n).+(\n    \),)/s";
    $replace[] = '$1' . ds_prepare_regions_configuration($regions) . '$2';
  }

  // Rewrite templates.
  drush_op('ds_file_preg_replace', array($layout_path . "/$twig_machine_name.html.twig", $layout_path . "/$machine_name.inc"), $find, $replace);

  // Notify user of the newly created templates.
  drush_print(dt('Templates for "@name" created in: @path', array('@name' => $name, '@path' => $layout_path)));
}

/**
 * Prepare a string for use as a valid machine name.
 */
function ds_prepare_machine_name($string) {
  $machine_name = str_replace(' ', '_', Unicode::strtolower($string));
  // Remove characters not valid in function names.
  $machine_name = preg_replace('/[^a-z0-9_]/', '', $machine_name);

  return $machine_name;
}

/**
 * Perform preg_replace() on the contents of an array of files.
 */
function ds_file_preg_replace($file_paths, $find, $replace) {
  foreach ($file_paths as $path) {
    $file_contents = file_get_contents($path);
    $file_contents = preg_replace($find, $replace, $file_contents);
    file_put_contents($path, $file_contents);
  }
}

/**
 * Prepare HTML structure for an array of regions.
 */
function ds_prepare_regions_html($region_names) {
  $output = array();

  foreach ($region_names as $name) {
    $machine_name = ds_prepare_machine_name($name);

    $output[] = <<<END
    <{{ \${$machine_name}_wrapper }} class="group-left{{ \${$machine_name}_classes }}">
      {% if \${$machine_name} is not null %}
        {{ \${$machine_name} }}
      {% endif %}
    </{{ \${$machine_name}_wrapper }}>
END;
  }

  return implode("\n\n", $output);
}

/**
 * Prepare variable documentation for an array of regions.
 */
function ds_prepare_regions_variable_documentation($region_names) {
  $output = array();

  foreach ($region_names as $name) {
    $machine_name = ds_prepare_machine_name($name);

    $output[] = <<<END
 *
 * - \$$machine_name: Rendered content for the "$name" region.
 * - \${$machine_name}_classes: String of classes that can be used to style the "$name" region.
END;
  }

  return implode("\n", $output);
}

/**
 * Prepare configuration for an array of regions.
 */
function ds_prepare_regions_configuration($region_names) {
  $output = array();

  foreach ($region_names as $name) {
    $machine_name = ds_prepare_machine_name($name);
    $output[] = "      '$machine_name' => t('$name'),";
  }

  return implode("\n", $output);
}

/**
 * Prepare styling for an array of regions.
 */
function ds_prepare_regions_css($region_names) {
  $output = array();

  foreach ($region_names as $name) {
    $html_class = Html::getClass($name);

    $output[] = <<<END
.ds-$html_class {
  /* Styles for the "$html_class" region go here */
}
END;
  }

  return implode("\n\n", $output);
}

/**
 * Create a Display Suite plugin.
 */
function drush_ds_field($name = NULL) {
  // Determine the layout name.
  if (!isset($name)) {
    $name = drush_get_option('name');
  }
  if (!$name) {
    drush_die(dt('You need to set a name for your field class'));
  }

  // We assume you run this command in the root of your module
  // Fetch the current path.
  $current_path = drush_cwd();

  // Fetch the module name from the path.
  $parts = explode('/', $current_path);
  $module_name = end($parts);

  // Class path.
  $class_path = $current_path . "/src/Plugin/DsField/";
  drush_mkdir($class_path);

  // Determine the path to our example layout templates.
  $ds_class_path = dirname(__FILE__);

  // Copy the example templates.
  drush_op('copy', $ds_class_path . '/ExampleField.php', $class_path . "/$name.php");

  // Prepare an array of things that need to be rewritten in our class.
  $find = array();
  $replace = array();

  // Replace example text.
  $find[] = '/ExampleField/';
  $replace[] = $name;
  $find[] = '/example_field/';
  $replace[] = $module_name;

  // Determine entity_type.
  $entity_type = drush_get_option('entity');
  if ($entity_type) {
    $find[] = '/entity_type = \"node\"/';
    $replace[] = 'entity_type = "' . $entity_type . '"';
  }

  // Rewrite class.
  drush_op('ds_file_preg_replace', array($class_path . "/$name.php"), $find, $replace);

  // Notify user of the newly created class.
  drush_print(dt('"@name" class created in: @class_path', array('@name' => $name, '@class_path' => $class_path)));
}
