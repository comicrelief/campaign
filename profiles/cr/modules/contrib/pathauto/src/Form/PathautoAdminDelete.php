<?php

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
      '#title' => $this->t('Choose aliases to delete'),
      '#tree' => TRUE,
    );

    // First we do the "all" case.
    $storage_helper = \Drupal::service('pathauto.alias_storage_helper');
    $total_count = $storage_helper->countAll();
    $form['delete']['all_aliases'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('All aliases'),
      '#default_value' => FALSE,
      '#description' => $this->t('Delete all aliases. Number of aliases which will be deleted: %count.', array('%count' => $total_count)),
    );

    // Next, iterate over all visible alias types.
    $definitions = $this->aliasTypeManager->getVisibleDefinitions();

    foreach ($definitions as $id => $definition) {
      /** @var \Drupal\pathauto\AliasTypeInterface $alias_type */
      $alias_type = $this->aliasTypeManager->createInstance($id);
      $count = $storage_helper->countBySourcePrefix($alias_type->getSourcePrefix());
      $form['delete']['plugins'][$id] = array(
        '#type' => 'checkbox',
        '#title' => (string) $definition['label'],
        '#default_value' => FALSE,
        '#description' => $this->t('Delete aliases for all @label. Number of aliases which will be deleted: %count.', array('@label' => (string) $definition['label'], '%count' => $count)),
      );
    }

    // Warn them and give a button that shows we mean business.
    $form['warning'] = array('#value' => '<p>' . t('<strong>Note:</strong> there is no confirmation. Be sure of your action before clicking the "Delete aliases now!" button.<br />You may want to make a backup of the database and/or the url_alias table prior to using this feature.') . '</p>');
    $form['buttons']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Delete aliases now!'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $storage_helper = \Drupal::service('pathauto.alias_storage_helper');
    if ($form_state->getValue(['delete', 'all_aliases'])) {
      $storage_helper->deleteAll();
      drupal_set_message($this->t('All of your path aliases have been deleted.'));
    }
    foreach (array_keys(array_filter($form_state->getValue(['delete', 'plugins']))) as $id) {
      $alias_type = $this->aliasTypeManager->createInstance($id);
      $storage_helper->deleteBySourcePrefix((string) $alias_type->getSourcePrefix());
      drupal_set_message($this->t('All of your %label path aliases have been deleted.', array('%label' => $alias_type->getLabel())));
    }
    $form_state->setRedirect('pathauto.admin.delete');
  }

}
