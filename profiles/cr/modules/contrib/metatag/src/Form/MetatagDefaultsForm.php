<?php

namespace Drupal\metatag\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MetatagDefaultsForm.
 *
 * @package Drupal\metatag\Form
 */
class MetatagDefaultsForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $metatag_defaults = $this->entity;
    $metatag_manager = \Drupal::service('metatag.manager');

    $form['#ajax_wrapper_id'] = 'metatag-defaults-form-ajax-wrapper';
    $ajax = [
      'wrapper' => $form['#ajax_wrapper_id'],
      'callback' => '::rebuildForm'
    ];
    $form['#prefix'] = '<div id="' . $form['#ajax_wrapper_id'] . '">';
    $form['#suffix'] = '</div>';

    $default_type = NULL;
    if (!empty($metatag_defaults)) {
      $default_type = $metatag_defaults->getOriginalId();
    }
    else {
      $form_state->set('default_type', $default_type);
    }

    $token_types = empty($default_type) ? [] : [explode('__', $default_type)[0]];

    // Add the token browser at the top.
    $form += \Drupal::service('metatag.token')->tokenBrowser($token_types);

    // If this is a new Metatag defaults, then list available bundles.
    if ($metatag_defaults->isNew()) {
      $options = $this->getAvailableBundles();
      $form['id'] = [
        '#type' => 'select',
        '#title' => t('Type'),
        '#description' => t('Select the type of default meta tags you would like to add.'),
        '#options' => $options,
        '#required' => TRUE,
        '#default_value' => $default_type,
        '#ajax' => $ajax + ['trigger_as' => ['name' => 'select_id_submit']]
      ];
      $form['select_id_submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#name' => 'select_id_submit',
        '#ajax' => $ajax,
        '#attributes' => [
          'class' => ['js-hide']
        ]
      ];
      $values = [];
    }
    else {
      $values = $metatag_defaults->get('tags');
    }

    // Add metatag form fields.
    $form = $metatag_manager->form($values, $form);

    return $form;
  }

  /**
   * Ajax form submit handler that will return the whole rebuilt form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function rebuildForm(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] == 'select_id_submit') {
      $form_state->set('default_type', $form_state->getValue('id'));
      $form_state->setRebuild();
    } else {
      parent::submitForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $metatag_defaults = $this->entity;

    // Set the label on new defaults.
    if ($metatag_defaults->isNew()) {
      $metatag_defaults_id = $form_state->getValue('id');
      list($entity_type, $entity_bundle) = explode('__', $metatag_defaults_id);
      // Get the entity label.
      $entity_manager = \Drupal::service('entity_type.manager');
      $entity_info = $entity_manager->getDefinitions();
      $entity_label = (string) $entity_info[$entity_type]->get('label');
      // Get the bundle label.
      $bundle_manager = \Drupal::service('entity_type.bundle.info');
      $bundle_info = $bundle_manager->getBundleInfo($entity_type);
      $bundle_label = $bundle_info[$entity_bundle]['label'];
      // Set the label to the config entity.
      $this->entity->set('label', $entity_label . ': ' . $bundle_label);
    }

    // Set tags within the Metatag entity.
    $tag_manager = \Drupal::service('plugin.manager.metatag.tag');
    $tags = $tag_manager->getDefinitions();
    $tag_values = [];
    foreach ($tags as $tag_id => $tag_definition) {
      if ($form_state->hasValue($tag_id)) {
        // Some plugins need to process form input before storing it.
        // Hence, we set it and then get it.
        $tag = $tag_manager->createInstance($tag_id);
        $tag->setValue($form_state->getValue($tag_id));
        if (!empty($tag->value())) {
          $tag_values[$tag_id] = $tag->value();
        }
      }
    }
    $metatag_defaults->set('tags', $tag_values);
    $status = $metatag_defaults->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Metatag defaults.', [
          '%label' => $metatag_defaults->label(),
        ]));
        break;
      default:
        drupal_set_message($this->t('Saved the %label Metatag defaults.', [
          '%label' => $metatag_defaults->label(),
        ]));
    }

    $form_state->setRedirectUrl($metatag_defaults->toUrl('collection'));
  }

  /**
   * Returns an array of available bundles to override.
   *
   * @return array
   *   A list of available bundles as $id => $label.
   */
  protected function getAvailableBundles() {
    $options = [];
    // @TODO discover supported entities.
    $entity_types = [
      'node' => 'Node',
      'taxonomy_term' => 'Taxonomy term',
    ];
    /** @var EntityTypeManagerInterface $entity_manager */
    $entity_manager = \Drupal::service('entity_type.manager');
    /** @var EntityTypeBundleInfoInterface $bundle_info */
    $bundle_info = \Drupal::service('entity_type.bundle.info');
    foreach ($entity_types as $entity_type => $entity_label) {
      $bundles = $bundle_info->getBundleInfo($entity_type);
      foreach ($bundles as $bundle_id => $bundle_metadata) {
        $metatag_defaults_id = $entity_type . '__' . $bundle_id;
        $metatags_defaults_manager = $entity_manager->getStorage('metatag_defaults');
        if (empty($metatags_defaults_manager->load($metatag_defaults_id))) {
          $options[$entity_label][$metatag_defaults_id] = $bundle_metadata['label'];
        }
      }
    }
    return $options;
  }

}
