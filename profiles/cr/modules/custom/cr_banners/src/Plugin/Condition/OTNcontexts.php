<?php

/**
 * @file
 * Contains Drupal\cr_banners\Plugin\Condition\OTNcontexts.
 */

namespace Drupal\cr_banners\Plugin\Condition;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'OTN context' condition.
 *
 * @Condition(
 *   id = "otn_contexts",
 *   label = @Translation("OTN Contexts"),
 * )
 */
class OTNcontexts extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['site_context'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('When the following context is enabled'),
      '#default_value' => $this->configuration['site_context'],
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', cr_banners_get_contexts()),
      '#description' => $this->t('If you select no context, the condition will evaluate to FALE for all contexts.'),
    );
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'site_context' => array(),
    ) + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['site_context'] = array_filter($form_state->getValue('site_context'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $context = array_intersect_key(cr_banners_get_contexts(), $this->configuration['site_context']);
    if (count($context) > 1) {
      $roles = implode(', ', $context);
    }
    else {
      $context = reset($context);
    }
    if (!empty($this->configuration['negate'])) {
      return $this->t('The current context is not set to @context', array('@context' => $context));
    }
    else {
      return $this->t('The current context is set to @context', array('@context' => $context));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $config = \Drupal::service('config.factory')->getEditable('cr_banners.contexts');
    $context = $config->get('current_context');
    if (in_array($context, $this->configuration['site_context'])) {
      return true;
    }
    else {
      return false;
    }
  }
}
