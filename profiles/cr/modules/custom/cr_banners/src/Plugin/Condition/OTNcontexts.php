<?php

/**
 * @file
 * Contains Drupal\cr_otn_banners\Plugin\Condition\OTNcontexts.
 */

namespace Drupal\cr_otn_banners\Plugin\Condition;
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
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', cr_otn_banners_contexts()),
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
    // Use the role labels. They will be sanitized below.
    $context = array_intersect_key(cr_otn_banners_contexts(), $this->configuration['site_context']);
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
    //if (empty($this->configuration['site_context']) && !$this->isNegated()) {
      //return TRUE;
    //}
    //$user = $this->getContextValue('user');
    //return (bool) array_intersect($this->configuration['site_context'], cr_otn_banners_contexts());
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Optimize cache context, if a user cache context is provided, only use
    // user.roles, since that's the only part this condition cares about.
    $contexts = [];
    foreach (parent::getCacheContexts() as $context) {
      $contexts[] = $context == 'user' ? 'user.roles' : $context;
    }
    return $contexts;
  }

}
