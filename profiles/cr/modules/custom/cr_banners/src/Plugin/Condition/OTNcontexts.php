<?php

/**
 * @file
 * Contains Drupal\cr_banners\Plugin\Condition\OTNcontexts.
 */

namespace Drupal\cr_banners\Plugin\Condition;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cr_banners\Context\contextHandler;

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
    $contexts = new contextHandler();
    $form['site_context'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('When the following context is enabled'),
      '#default_value' => $this->configuration['site_context'],
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', $contexts->getContexts()),
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
    $contexts = new contextHandler();
    $context = array_intersect_key($contexts->getContexts(), $this->configuration['site_context']);
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
    $context_handler = new contextHandler();
    $context = $context_handler->getCurrentContext();
    if (in_array($context, $this->configuration['site_context'])) {
      return true;
    }
    else {
      return false;
    }
  }
}
