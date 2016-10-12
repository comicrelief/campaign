<?php

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Base for controller for form access.
 */
class YamlFormEntityAccessForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->entity;
    $access = $yamlform->getAccessRules();
    $roles = array_map('\Drupal\Component\Utility\Html::escape', user_role_names());
    $permissions = [
      'create' => $this->t('Create form submissions'),
      'view_any' => $this->t('View all form submissions'),
      'update_any' => $this->t('Update all form submissions'),
      'delete_any' => $this->t('Delete all form submissions'),
      'purge_any' => $this->t('Purge all form submissions'),
      'view_own' => $this->t('View own form submissions'),
      'update_own' => $this->t('Update own form submissions'),
      'delete_own' => $this->t('Delete own form submissions'),
    ];
    $form['access']['#tree'] = TRUE;
    foreach ($permissions as $name => $title) {
      $access_roles = $roles;
      // Only allow 'anonymous' users to create submissions.
      if ($name != 'create') {
        unset($access_roles['anonymous']);
      }

      $form['access'][$name] = [
        '#type' => 'details',
        '#title' => $title,
        '#open' => ($access[$name]['roles'] || $access[$name]['users']) ? TRUE : FALSE,
      ];
      $form['access'][$name]['roles'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Roles'),
        '#options' => $access_roles,
        '#default_value' => $access[$name]['roles'],
      ];
      $form['access'][$name]['users'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('Users'),
        '#target_type' => 'user',
        '#tags' => TRUE,
        '#default_value' => $access[$name]['users'] ? User::loadMultiple($access[$name]['users']) : [],
      ];
    }

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = parent::actionsElement($form, $form_state);
    // Don't display delete button.
    unset($element['delete']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $access = $form_state->getValue('access');

    // Cleanup roles and users.
    foreach ($access as &$settings) {
      // Filter roles.
      $settings['roles'] = array_values(array_filter($settings['roles']));
      // Convert target_ids to a simple list of uids.
      if ($settings['users']) {
        foreach ($settings['users'] as $index => $item) {
          $settings['users'][$index] = $item['target_id'];
        }
      }
      else {
        $settings['users'] = [];
      }
    }

    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();
    $yamlform->setAccessRules($access);
    $yamlform->save();

    $this->logger('yamlform')->notice('Form access @label saved.', ['@label' => $yamlform->label()]);
    drupal_set_message($this->t('Form access %label saved.', ['%label' => $yamlform->label()]));
  }

}
