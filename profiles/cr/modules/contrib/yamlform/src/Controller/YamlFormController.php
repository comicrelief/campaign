<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormController.
 */

namespace Drupal\yamlform\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides route responses for YAML form.
 */
class YamlFormController extends ControllerBase {

  /**
   * Returns a form to add a new submission to a YAML form.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form this submission will be added to.
   *
   * @return array
   *   The YAML form submission form.
   */
  public function addForm(Request $request, YamlFormInterface $yamlform) {
    return $yamlform->getSubmissionForm();
  }

  /**
   * Returns a YAML form confirmation page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form.
   *
   * @return array
   *   A render array represent a YAML form confirmation page
   */
  public function confirmation(Request $request, YamlFormInterface $yamlform) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    if ($token = $request->get('token')) {
      /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $yamlform_submission_storage */
      $yamlform_submission_storage = $this->entityManager()->getStorage('yamlform_submission');
      $entities = $yamlform_submission_storage->loadByProperties(['token' => $token]);
      $yamlform_submission = reset($entities);
    }
    else {
      $yamlform_submission = NULL;
    }

    $settings = $yamlform->getSettings();

    $build = [];

    $build['#yamlform'] = $yamlform;
    $build['#yamlform_submission'] = $yamlform_submission;

    $build['#title'] = $yamlform->label();

    $build['confirmation'] = [
      '#markup' => $settings['confirmation_message'],
      '#allowed_tags' => Xss::getAdminTagList(),
    ];

    // Link back to the source URL or the main YAML form.
    if ($yamlform_submission) {
      $url = $yamlform_submission->getSourceUrl();
    }
    else {
      // Apply all passed query string parameters to the 'Back to form' link.
      $query = $request->query->all();
      unset($query['yamlform_id']);
      $options = ($query) ? ['query' => $query] : [];
      $url = $yamlform->toUrl('canonical', $options);
    }
    if ($url) {
      $build['back_to'] = [
        '#prefix' => '<p>',
        '#suffix' => '</p>',
        '#type' => 'link',
        '#title' => $this->t('Back to form'),
        '#url' => $url,
      ];
    }

    return $build;
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form.
   *
   * @return string
   *   The YAML form label as a render array.
   */
  public function title(YamlFormInterface $yamlform) {
    return $yamlform->label();
  }

}
