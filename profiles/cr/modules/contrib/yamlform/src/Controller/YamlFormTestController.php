<?php

/**
 * @file
 * Contains \Drupal\yamlform\Controller\YamlFormTestController.
 */

namespace Drupal\yamlform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormSubmissionGenerate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for YAML form testing.
 */
class YamlFormTestController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * YAML form submission generation service.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionGenerate
   */
  protected $generate;

  /**
   * Constructs a new YamlFormTestController object.
   *
   * @param \Drupal\yamlform\YamlFormSubmissionGenerate $submission_generate
   *   The YAML form submission generation service.
   */
  public function __construct(YamlFormSubmissionGenerate $submission_generate) {
    $this->generate = $submission_generate;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('yamlform_submission.generate')
    );
  }

  /**
   * Returns a form to add a new test submission to a YAML form.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The YAML form this submission will be added to.
   *
   * @return array
   *   The YAML form submission form.
   */
  public function testForm(Request $request, YamlFormInterface $yamlform) {
    if ($request->query->get('yamlform_id') == $yamlform->id()) {
      return $yamlform->getSubmissionForm();
    }

    return $yamlform->getSubmissionForm(['data' => $this->generate->getData($yamlform)]);
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
    return $this->t('Testing %title form', ['%title' => $yamlform->label()]);
  }

}
