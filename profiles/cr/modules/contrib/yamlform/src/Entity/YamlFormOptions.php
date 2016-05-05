<?php

/**
 * @file
 * Contains Drupal\yamlform\Entity\YamlFormOptions.
 */

namespace Drupal\yamlform\Entity;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\yamlform\YamlFormOptionsInterface;

/**
 * Defines the YAML form options entity.
 *
 * @ConfigEntityType(
 *   id = "yamlform_options",
 *   label = @Translation("YAML form options"),
 *   handlers = {
 *     "access" = "Drupal\yamlform\YamlFormOptionsAccessControlHandler",
 *     "list_builder" = "Drupal\yamlform\YamlFormOptionsListBuilder",
 *     "form" = {
 *       "default" = "Drupal\yamlform\YamlFormOptionsForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     }
 *   },
 *   admin_permission = "administer yamlform",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/yamlform/settings/options/manage/{yamlform_options}",
 *     "add-form" = "/admin/structure/yamlform/settings/options/add",
 *     "edit-form" = "/admin/structure/yamlform/settings/options/manage/{yamlform_options}",
 *     "delete-form" = "/admin/structure/yamlform/settings/options/manage/{yamlform_options}/delete",
 *     "collection" = "/admin/structure/yamlform/settings/options/manage",
 *   },
 *   config_export = {
 *     "id",
 *     "uuid",
 *     "label",
 *     "options",
 *   }
 * )
 */
class YamlFormOptions extends ConfigEntityBase implements YamlFormOptionsInterface {

  /**
   * The YAML form options ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The YAML form options UUID.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The YAML form options label.
   *
   * @var string
   */
  protected $label;

  /**
   * The YAML form options options.
   *
   * @var string
   */
  protected $options;

  /**
   * The YAML form options decoded.
   *
   * @var string
   */
  protected $optionsDecoded;

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    if (!isset($this->optionsDecoded)) {
      try {
        $options = Yaml::decode($this->options);
        // Since YAML supports simple values.
        $options = (is_array($options)) ? $options : [];
      }
      catch (\Exception $exception) {
        $link = $this->link(t('Edit'), 'edit-form');
        \Drupal::logger('yamlform')->notice('%title options are not valid. @message', ['%title' => $this->label(), '@message' => $exception->getMessage(), 'link' => $link]);
        $options = FALSE;
      }
      $this->optionsDecoded = $options;
    }
    return $this->optionsDecoded;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Clear cached properties.
    $this->optionsDecoded = NULL;
  }

}
