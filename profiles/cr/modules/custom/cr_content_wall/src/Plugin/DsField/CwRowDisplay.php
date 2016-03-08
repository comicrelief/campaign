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
   * @param array of referenced block id's
   * @return array of rendered blocks in row defined view modes
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
   * @param string: row block view mode
   * @return array of associated child block view modes
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
   * @param row block id
   * @return loaded BlockContent object
   */
  public function getRowEntity($id) {
    return BlockContent::load($id);
  }

  /**
   * @param loaded row block entity
   * @param reference field machine name
   * @return array of referenced block id's
   */
  public function getReferencedBlocks($block, $field) {
    $field_values = $block->get($field)->getValue();
    $blocks = array();

    foreach ($field_values as $referenced_block) {
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
    $options['field_cw_block_reference'] = 'field_cw_block_reference';

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

}
