<?php

namespace Drupal\yamlform\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\yamlform\YamlFormHandlerInterface;

/**
 * Defines a form handler annotation object.
 *
 * Plugin Namespace: Plugin\YamlFormHandler.
 *
 * For a working example, see
 * \Drupal\yamlform\Plugin\YamlFormHandler\EmailYamlFormHandler
 *
 * @see hook_yamlform_handler_info_alter()
 * @see \Drupal\yamlform\YamlFormHandlerInterface
 * @see \Drupal\yamlform\YamlFormHandlerBase
 * @see \Drupal\yamlform\YamlFormHandlerManager
 * @see plugin_api
 *
 * @Annotation
 */
class YamlFormHandler extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the form handler.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The category in the admin UI where the block will be listed.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $category = '';

  /**
   * A brief description of the form handler.
   *
   * This will be shown when adding or configuring this form handler.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description = '';

  /**
   * The maximum number of instances allowed for this form handler.
   *
   * Possible values are positive integers or
   * \Drupal\yamlform\YamlFormHandlerInterface::CARDINALITY_UNLIMITED or
   * \Drupal\yamlform\YamlFormHandlerInterface::CARDINALITY_SINGLE.
   *
   * @var int
   */
  public $cardinality = YamlFormHandlerInterface::CARDINALITY_UNLIMITED;

  /**
   * Notifies the form that this handler processes results.
   *
   * When set to TRUE, 'Disable saving of submissions.' can be set.
   *
   * @var bool
   */
  public $results = YamlFormHandlerInterface::RESULTS_IGNORED;

}
