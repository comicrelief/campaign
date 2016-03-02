<?php
/**
 * @file
 * Contains \Drupal\cr_content_wall\Plugin\DsField\CwRowDisplay.
 */

namespace Drupal\cr_content_wall\Plugin\DsField;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\block_content\Entity\BlockContent;
use Drupal\field\FieldConfigInterface;

/**
 * @DsField(
 *   id = "cr_content_wall_CwRowDisplay",
 *   title = @Translation("Row Display"),
 *   entity_type = "block_content",
 *   provider = "cr_content_wall"
 * )
 */
class CwRowDisplay extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $row_id = $this->entity()->id();

    $config['reference_field'] = 'field_cw_block_reference';
    // Get row block
    $row = $this->getRowEntity($row_id);
    // Get referenced content blocks
    $blocks = $this->getReferencedBlocks($row, $config['reference_field']);
    //die(print_r(,1));


  }

  public function getReferencedBlocks($block, $field) {
    $block_field = $block->get($field)->getValue();
    // Get list
    return array();
  }

  public function getRowEntity($id) {
    return BlockContent::load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $options = array();
    $options[] = 'field_cw_block_reference';

    $settings['reference_field'] = array(
      '#type' => 'select',
      '#title' => t('Reference Field'),
      '#default_value' => $config['reference_field'],
      '#options' => $options,
    );

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();
    $no_selection = array('No reference field selected.');

    if (isset($config['reference_field']) && $config['reference_field']) {
      return array('Field: ' . $config['reference_field']);
    }

    return $no_selection;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = array(
      'reference_field' => 'field_cw_block_reference',
    );

    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function formatters() {
    return array();
  }

}
