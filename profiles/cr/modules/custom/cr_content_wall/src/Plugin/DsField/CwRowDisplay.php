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
    if (!isset($config['reference_field']) || !$config['reference_field']) {
      return;
    }

    $row_id = $this->entity()->id();

    // Get row block
    $row = $this->getRowEntity($row_id);
    // Get referenced content blocks
    $blocks = $this->getReferencedBlocks($row, $config['reference_field']);

    return array(
      '#theme' => 'item_list',
      '#items' => $this->buildRenderedBlocks($blocks),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildRenderedBlocks($blocks) {
    if (!isset($blocks) || !$blocks) {
      return;
    }

    $rendered_blocks = array();
    $row_view_mode = $this->viewMode();
    $view_modes = $this->getBlockViewModes($row_view_mode);

    foreach ($blocks as $key => $id) {
      $block = BlockContent::load($id);

      if (isset($view_modes[$key])) {
        $display = \Drupal::entityManager()->
          getViewBuilder('block_content')->view($block, $view_modes[$key]);

        $rendered_blocks[] = $display;
      }
    }
    return $rendered_blocks;
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockViewModes($view_mode) {
    $view_modes = array(
      'cw_1col_l' => array('cw_l'),
      'cw_2col_m_m' => array('cw_m', 'cw_m'),
      'cw_2col_s_m' => array('cw_s', 'cw_mp'),
      'cw_2col_m_s' => array('cw_mp', 'cw_s'),
      'cw_3col_s_s_s' => array('cw_s', 'cw_s', 'cw_s'),
    );

    return $view_modes[$view_mode];
  }

  /**
   * {@inheritdoc}
   */
  public function getRowEntity($id) {
    return BlockContent::load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function getReferencedBlocks($block, $field) {
    $field_values = $block->get($field)->getValue();
    $blocks = array();

    foreach ($field_values as $key => $referenced_block) {
      $blocks[] = $referenced_block['target_id'];
    }

    return $blocks;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $options = array();
    $options = $this->getReferenceFields();

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
  public function getReferenceFields() {
    $field_options = array();
    $field_options[] = 'field_cw_block_reference';
    return $field_options;
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
