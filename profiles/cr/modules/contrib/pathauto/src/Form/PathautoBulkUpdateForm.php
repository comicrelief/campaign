<?php

/**
 * @file
 * Contains \Drupal\pathauto\Form\PathautoBulkUpdateForm.
 */

namespace Drupal\pathauto\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pathauto\AliasTypeBatchUpdateInterface;
use Drupal\pathauto\AliasTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure file system settings for this site.
 */
class PathautoBulkUpdateForm extends FormBase {

  /**
   * The alias type manager.
   *
   * @var \Drupal\pathauto\AliasTypeManager
   */
  protected $aliasTypeManager;

  /**
   * Constructs a PathautoBulkUpdateForm object.
   *
   * @param \Drupal\pathauto\AliasTypeManager $alias_type_manager
   *   The alias type manager.
   */
  public function __construct(AliasTypeManager $alias_type_manager) {
    $this->aliasTypeManager = $alias_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.alias_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pathauto_bulk_update_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = array();

    $form['#update_callbacks'] = array();

    $form['update'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Select the types of un-aliased paths for which to generate URL aliases'),
      '#options' => array(),
      '#default_value' => array(),
    );

    $definitions = $this->aliasTypeManager->getDefinitions();

    foreach ($definitions as $id => $definition) {
      $alias_type = $this->aliasTypeManager->createInstance($id);
      if ($alias_type instanceof AliasTypeBatchUpdateInterface) {
        $form['update']['#options'][$id] = $alias_type->getLabel();
      }
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Update'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = array(
      'title' => t('Bulk updating URL aliases'),
      'operations' => array(
        array('Drupal\pathauto\Form\PathautoBulkUpdateForm::batchStart', array()),
      ),
      'finished' => 'Drupal\pathauto\Form\PathautoBulkUpdateForm::batchFinished',
    );

    foreach ($form_state->getValue('update') as $id) {
      if (!empty($id)) {
        $batch['operations'][] = array('Drupal\pathauto\Form\PathautoBulkUpdateForm::batchProcess', array($id));
      }
    }

    batch_set($batch);
  }

  /**
   * Batch callback; count the current number of URL aliases for comparison later.
   */
  public static function batchStart(&$context) {
    $context['results']['count_before'] = db_select('url_alias')->countQuery()->execute()->fetchField();
  }

  /**
   * Common batch processing callback for all operations.
   *
   * Required to load our include the proper batch file.
   */
  public static function batchProcess($id, &$context) {
    /** @var \Drupal\pathauto\AliasTypeBatchUpdateInterface $alias_type */
    $alias_type = \Drupal::service('plugin.manager.alias_type')->createInstance($id);
    $alias_type->batchUpdate($context);
  }

  /**
   * Batch finished callback.
   */
  public static function batchFinished($success, $results, $operations) {
    if ($success) {
      // Count the current number of URL aliases after the batch is completed
      // and compare to the count before the batch started.
      $results['count_after'] = db_select('url_alias')->countQuery()->execute()->fetchField();
      $results['count_changed'] = max($results['count_after'] - $results['count_before'], 0);
      if ($results['count_changed']) {
        drupal_set_message(\Drupal::translation()->formatPlural($results['count_changed'], 'Generated 1 URL alias.', 'Generated @count URL aliases.'));
      }
      else {
        drupal_set_message(t('No new URL aliases to generate.'));
      }
    }
    else {
      $error_operation = reset($operations);
      drupal_set_message(t('An error occurred while processing @operation with arguments : @args', array('@operation' => $error_operation[0], '@args' => print_r($error_operation[0], TRUE))));
    }
  }

}
