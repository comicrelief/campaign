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
use Drupal\node\Entity\Node;
use Drupal\block\Entity\Block;
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
    $view_mode = $this->viewMode();

    //$node = Node::load('4');
    //$node_field = $node->get('field_cw_row_selector')->getValue();

    $block = Block::load('1');
    //$block_field = $block->get('field_teaser_title')->getValue();

    //die('z:'.print_r($block,1));
  }


  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();

    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {

    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function formatters() {
    return array();
  }

}
