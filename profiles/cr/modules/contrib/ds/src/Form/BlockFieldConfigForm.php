<?php

/**
 * @file
 * Contains \Drupal\ds\Form\BlockFieldConfigForm.
 */

namespace Drupal\ds\Form;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure block fields.
 */
class BlockFieldConfigForm extends FieldFormBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $field_key = '') {
    // Fetch field.
    $field = $this->config('ds.field.' . $field_key)->get();

    // Save the field for future reuse.
    $this->field = $field;

    // Create an instance of the block.
    /** @var $block BlockPluginInterface */
    $manager = \Drupal::service('plugin.manager.block');
    $block_id = $field['properties']['block'];
    $block = $manager->createInstance($block_id);

    // Set block config form default values.
    if (isset($field['properties']['config'])) {
      $block->setConfiguration($field['properties']['config']);
    }

    // Get block config form.
    $form = $block->blockForm($form, $form_state);

    if (!$form) {
      return array('#markup' => $this->t("This block has no configuration options."));
    }

    // Some form items require this (core block manager also sets this).
    $form['#tree'] = TRUE;

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#weight' => 100,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $field = $this->field;

    // Create an instance of the block.
    /** @var $block BlockPluginInterface */
    $manager = \Drupal::service('plugin.manager.block');
    $block_id = $field['properties']['block'];
    $block = $manager->createInstance($block_id);

    // Validate block config data using the block's validation handler.
    $block->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $field = $this->field;

    // Create an instance of the block.
    /** @var $block BlockPluginInterface */
    $manager = \Drupal::service('plugin.manager.block');
    $block_id = $field['properties']['block'];
    $block = $manager->createInstance($block_id);

    // Process block config data using the block's submit handler.
    $block->blockSubmit($form, $form_state);
    $block_config = $block->getConfiguration();

    // Clear cache tags
    $this->cacheInvalidator->invalidateTags($block->getCacheTags());

    // Save block config
    $this->config('ds.field.' . $field['id'])->set('properties.config', $block_config)->save();

    // Clear caches and redirect
    $this->finishSubmitForm($form, $form_state);
  }

}
