<?php

/**
 * @file
 * Contains \Drupal\pathauto\Form\PathautoAdminDelete.
 */

namespace Drupal\pathauto\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pathauto\AliasTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alias mass delete form.
 */
class PathautoAdminDelete extends FormBase {

  /**
   * The alias type manager.
   *
   * @var \Drupal\pathauto\AliasTypeManager
   */
  protected $aliasTypeManager;

  /**
   * Constructs a PathautoAdminDelete object.
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
    return 'pathauto_admin_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['delete'] = array(
      '#type' => 'fieldset',
      '#title' => t('Choose aliases to delete'),
      '#tree' => TRUE,
    );

    // First we do the "all" case.
    $total_count = db_query('SELECT count(1) FROM {url_alias}')->fetchField();
    $form['delete']['all_aliases'] = array(
      '#type' => 'checkbox',
      '#title' => t('All aliases'),
      '#default_value' => FALSE,
      '#description' => t('Delete all aliases. Number of aliases which will be deleted: %count.', array('%count' => $total_count)),
    );

    // Next, iterate over all alias types
    $definitions = $this->aliasTypeManager->getDefinitions();

    foreach ($definitions as $id => $definition) {
      /** @var \Drupal\pathauto\AliasTypeInterface $alias_type */
      $alias_type = $this->aliasTypeManager->createInstance($id);
      $count = db_query("SELECT count(1) FROM {url_alias} WHERE source LIKE :src", array(':src' => $alias_type->getSourcePrefix() . '%'))->fetchField();
      $form['delete']['plugins'][$id] = array(
        '#type' => 'checkbox',
        '#title' => (string) $definition['label'],
        '#default_value' => FALSE,
        '#description' => t('Delete aliases for all @label. Number of aliases which will be deleted: %count.', array('@label' => (string) $definition['label'], '%count' => $count)),
      );
    }

    // Warn them and give a button that shows we mean business.
    $form['warning'] = array('#value' => '<p>' . t('<strong>Note:</strong> there is no confirmation. Be sure of your action before clicking the "Delete aliases now!" button.<br />You may want to make a backup of the database and/or the url_alias table prior to using this feature.') . '</p>');
    $form['buttons']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Delete aliases now!'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue(['delete', 'all_aliases'])) {
      db_delete('url_alias')
        ->execute();
      drupal_set_message($this->t('All of your path aliases have been deleted.'));
    }
    foreach (array_keys(array_filter($form_state->getValue(['delete', 'plugins']))) as $id) {
      /** @var \Drupal\pathauto\AliasTypeInterface $alias_type */
      $alias_type = $this->aliasTypeManager->createInstance($id);
      db_delete('url_alias')
        ->condition('source', db_like($alias_type->getSourcePrefix()) . '%', 'LIKE')
        ->execute();
      drupal_set_message(t('All of your %label path aliases have been deleted.', array('%label' => $alias_type->getLabel())));
    }
    $form_state->setRedirect('pathauto.admin.delete');
  }

}
