<?php

/**
 * @file
 * Contains \Drupal\yamlform\Form\YamlFormSubmissionDeleteForm.
 */

namespace Drupal\yamlform\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Url;

/**
 * Provides a confirmation form for deleting a YAML form submission.
 */
class YamlFormSubmissionDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t(
      'Are you sure you want to delete @title: Submission #@id?', ['@title' => $this->getEntity()->getYamlForm()->label(), '@id' => $this->getEntity()->id()]
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    return $this->t(
      '@title: Submission #@id has been deleted.', ['@title' => $this->getEntity()->getYamlForm()->label(), '@id' => $this->getEntity()->id()]
    );
  }


  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl() {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $this->getEntity();
    $yamlform = $yamlform_submission->getYamlForm();

    if ($yamlform->access('submission_view_any')) {
      return $yamlform->toUrl('results-submissions');
    }
    elseif ($yamlform->access('submission_view_own')) {
      return $yamlform->toUrl('submissions');
    }
    else {
      // Otherwise fall back to the front page.
      return Url::fromRoute('<front>');
    }
  }

}
