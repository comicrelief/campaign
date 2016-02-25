<?php

/**
 * @file
 * Contains \Drupal\ds\Form\BlockFieldForm.
 */

namespace Drupal\ds\Form;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configure block fields.
 */
class BlockFieldForm extends FieldFormBase implements ContainerInjectionInterface {

  /**
   * The type of the dynamic ds field
   */
  const TYPE = 'block';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ds_field_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $field_key = '') {
    $form = parent::buildForm($form, $form_state, $field_key);

    $field = $this->field;

    $manager = \Drupal::service('plugin.manager.block');

    $blocks = array();
    foreach ($manager->getDefinitions() as $plugin_id => $plugin_definition) {
      $blocks[$plugin_id] = $plugin_definition['admin_label'];
    }
    asort($blocks);

    $form['block_identity']['block'] = array(
      '#type' => 'select',
      '#options' => $blocks,
      '#title' => t('Block'),
      '#required' => TRUE,
      '#default_value' => isset($field['properties']['block']) ? $field['properties']['block'] : '',
    );

    $form['use_block_title'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use block title as the field label'),
      '#default_value' => isset($field['properties']['use_block_title']) ? $field['properties']['use_block_title'] : FALSE,
      '#weight' => 90,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getProperties(FormStateInterface $form_state) {
    $properties['block'] = $form_state->getValue('block');

    // Preserve existing block config.
    $field_key = $form_state->getValue('id');
    $field = $this->config('ds.field.' . $field_key)->get();
    if (isset($field['properties']) && ($field['properties']['block'] == $properties['block'])) {
      $properties = $field['properties'];
    }

    // Save title checkbox
    $properties['use_block_title'] = $form_state->getValue('use_block_title');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return BlockFieldForm::TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeLabel() {
    return 'Block field';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Create an instance of the block to find out if it has a config form.
    // Redirect to the block config form if there is one.
    /** @var $block BlockPluginInterface */
    $manager = \Drupal::service('plugin.manager.block');
    $block_id = $this->field['properties']['block'];
    $block = $manager->createInstance($block_id);
    $block_config_form = $block->blockForm([], new FormState());
    if ($block_config_form) {
      $url = new Url('ds.manage_block_field_config', array('field_key' => $this->field['id']));
      $form_state->setRedirectUrl($url);
    }
  }

}
