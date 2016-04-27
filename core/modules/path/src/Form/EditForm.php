<?php

/**
 * @file
 * Contains \Drupal\path\Form\EditForm.
 */

namespace Drupal\path\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides the path edit form.
 */
class EditForm extends PathFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'path_admin_edit';
  }

  /**
   * {@inheritdoc}
   */
  protected function buildPath($pid) {
    return $this->aliasStorage->load(array('pid' => $pid));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $pid = NULL) {
    $form = parent::buildForm($form, $form_state, $pid);

    $form['#title'] = $this->path['alias'];
    $form['pid'] = array(
      '#type' => 'hidden',
      '#value' => $this->path['pid'],
    );
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#submit' => array('::deleteSubmit'),
    );
    return $form;
  }

  /**
   * Submits the delete form.
   */
  public function deleteSubmit(array &$form, FormStateInterface $form_state) {
    $url = new Url('path.delete', array(
      'pid' => $form_state->getValue('pid'),
    ));

    if ($this->getRequest()->query->has('destination')) {
      $url->setOption('query', $this->getDestinationArray());
      $this->getRequest()->query->remove('destination');
    }

    $form_state->setRedirectUrl($url);
  }

}
