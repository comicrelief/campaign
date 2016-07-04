<?php

namespace Drupal\cr_content_wall\Plugin\DsField;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\block_content\Entity\BlockContent;

/**
 * Custom Row Display.
 *
 * Custom display field to rendered all referenced items in view modes.
 *
 * @author Zach Bimson <zach.bimson@gmail.com>
 *
 * @DsField(
 *   id = "cr_content_wall_CwRowDisplay",
 *   title = @Translation("Row Display"),
 *   description = @Translation("Custom DS field to manage row display"),
 *   entity_type = "block_content",
 *   provider = "cr_content_wall",
 *   ui_limit = {"cw_row_block|*"}
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
    // Get row block.
    $row = $this->getRowEntity($row_id);
    // Get referenced content blocks.
    $blocks = $this->getReferencedBlocks($row, $config['reference_field']);

    return array(
      '#theme' => 'item_list',
      '#items' => $this->buildRenderedBlocks($row, $blocks),
    );
  }

  /**
   * Returns array of rendered content blocks.
   *
   * Loads blocks from passed id's and loads them in the correct view modes.
   *
   * @param array $blocks
   *   An array of referenced block id's.
   *
   * @return array
   *   Array of rendered blocks in row defined view modes.
   */
  public function buildRenderedBlocks($row, $blocks) {
    if (!isset($blocks) || !$blocks) {
      return [];
    }

    $rendered_blocks = array();
    $row_view_mode = $row->get('field_cw_view_mode')->getValue();
    // Need a better way to get array value below.
    $view_modes = $this->getBlockViewModes($row_view_mode[0]['value']);

    foreach ($blocks as $key => $block_id) {
      $block = BlockContent::load($block_id);

      if (isset($view_modes[$key])) {
        $view = \Drupal::entityManager()->getViewBuilder('block_content');
        $display = $view->view($block, $view_modes[$key]);

        $rendered_blocks[] = $display;
      }
    }
    return $rendered_blocks;
  }

  /**
   * Return an array of child block view modes.
   *
   * Passing the row block's view mode we return the associated array of child
   * block view modes.
   *
   * @param string $view_mode
   *   Row block view mode as string.
   *
   * @return array
   *   Array of associated child block view modes.
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
   * Get row block entity.
   *
   * Return loaded BlockContent entity.
   *
   * @param string $row_id
   *   Row block id.
   *
   * @return object
   *   Loaded BlockContent object.
   */
  public function getRowEntity($row_id) {
    return BlockContent::load($row_id);
  }

  /**
   * Get array of referenced blocks.
   *
   * Method for returning array of child block id's.
   *
   * @param object $block
   *   Loaded row block entity.
   * @param string $field
   *   Reference field machine name.
   *
   * @return array
   *   Referenced block id's.
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
