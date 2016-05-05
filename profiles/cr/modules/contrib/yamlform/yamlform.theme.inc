<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

use Drupal\Core\Link;
use Drupal\Component\Serialization\Yaml;
use Drupal\yamlform\Utility\YamlFormTidy;
use Drupal\yamlform\Utility\YamlFormDateHelper;
use Drupal\yamlform\Element\YamlFormCodeMirrorBase;

/**
 * Prepares variables for yamlform templates.
 *
 * Default template: yamlform.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #action, #method, #attributes, #children.
 */
function template_preprocess_yamlform(&$variables) {
  template_preprocess_form($variables);
}

/**
 * Prepares variables for YAML form submission navigation templates.
 *
 * Default template: yamlform-submission-navigation.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A YAML form submission.
 *   - rel: YAML form submission link template.
 *          (canonical, edit-form, resend-form, html, text, or yaml).
 */
function template_preprocess_yamlform_submission_navigation(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $yamlform_submission_storage */
  $yamlform_submission_storage = \Drupal::entityManager()->getStorage('yamlform_submission');
  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];

  $rel = $variables['rel'];
  if (!$yamlform_submission->hasLinkTemplate($rel)) {
    $rel = 'canonical';
  }

  if ($previous_submission = $yamlform_submission_storage->getPreviousSubmission($yamlform_submission, \Drupal::currentUser())) {
    $variables['prev_url'] = $previous_submission->toUrl($rel)->toString();
  }
  if ($next_submission = $yamlform_submission_storage->getNextSubmission($yamlform_submission, \Drupal::currentUser())) {
    $variables['next_url'] = $next_submission->toUrl($rel)->toString();
  }

  $variables['#attached']['library'][] = 'yamlform/yamlform.navigation';
}

/**
 * Prepares variables for YAML form submission information template.
 *
 * Default template: yamlform-submission-information.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A YAML form submission.
 */
function template_preprocess_yamlform_submission_information(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];

  $variables['sid'] = $yamlform_submission->id();
  $variables['uuid'] = $yamlform_submission->uuid();
  $variables['is_draft'] = $yamlform_submission->isDraft() ? t('Yes') : t('No');
  $variables['remote_addr'] = $yamlform_submission->getRemoteAddr();
  $variables['submitted_by'] = $yamlform_submission->getOwner()->toLink();
  $variables['form'] = $yamlform_submission->getYamlForm()->toLink();

  $variables['created'] = YamlFormDateHelper::format($yamlform_submission->getCreatedTime());
  $variables['completed'] = YamlFormDateHelper::format($yamlform_submission->getCompletedTime());
  $variables['changed'] = YamlFormDateHelper::format($yamlform_submission->getChangedTime());

  // @see \Drupal\Core\Field\Plugin\Field\FieldFormatter\LanguageFormatter::viewValue()
  $languages = \Drupal::languageManager()->getNativeLanguages();
  $langcode = $yamlform_submission->get('langcode')->value;
  $variables['language'] = isset($languages[$langcode]) ? $languages[$langcode]->getName() : $langcode;

  if ($source_url = $yamlform_submission->getSourceUrl()) {
    $variables['uri'] = Link::fromTextAndUrl($source_url->setAbsolute(FALSE)->toString(), $source_url);;
  }

  if ($source_entity = $yamlform_submission->getSourceEntity()) {
    $variables['submitted_to'] = $source_entity->toLink();
  }

  $variables['submission_view_any'] = $yamlform_submission->getYamlForm()->access('submission_view_any');
}

/**
 * Prepares variables for YAML form submission HTML template.
 *
 * Default template: yamlform-submission-html.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A YAML form submission.
 */
function template_preprocess_yamlform_submission_html(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];

  /** @var \Drupal\yamlform\YamlFormSubmissionViewBuilder $view_builder */
  $view_builder = \Drupal::entityManager()->getViewBuilder('yamlform_submission');

  $yamlform = $yamlform_submission->getYamlForm();
  $data = $yamlform_submission->getData();
  $inputs = $yamlform->getInputs();
  $variables['data'] = $view_builder->buildInputs($inputs, $data);
}

/**
 * Prepares variables for YAML form submission plain text template.
 *
 * Default template: yamlform-submission-text.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A YAML form submission.
 */
function template_preprocess_yamlform_submission_text(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];

  $variables['sid'] = $yamlform_submission->id();
  $variables['uuid'] = $yamlform_submission->uuid();
  $variables['is_draft'] = $yamlform_submission->isDraft() ? t('Yes') : t('No');
  $variables['remote_addr'] = $yamlform_submission->getRemoteAddr();
  $variables['submitted_by'] = $yamlform_submission->getOwner()->label();
  $variables['form'] = $yamlform_submission->getYamlForm()->label();

  $variables['created'] = YamlFormDateHelper::format($yamlform_submission->getCreatedTime());
  $variables['completed'] = YamlFormDateHelper::format($yamlform_submission->getCompletedTime());
  $variables['changed'] = YamlFormDateHelper::format($yamlform_submission->getChangedTime());

  // @see \Drupal\Core\Field\Plugin\Field\FieldFormatter\LanguageFormatter::viewValue()
  $languages = \Drupal::languageManager()->getNativeLanguages();
  $langcode = $yamlform_submission->get('langcode')->value;
  $variables['language'] = isset($languages[$langcode]) ? $languages[$langcode]->getName() : $langcode;

  if ($source_url = $yamlform_submission->getSourceUrl()) {
    $variables['uri'] = $source_url->toString();
  }

  if ($source_entity = $yamlform_submission->getSourceEntity()) {
    $variables['submitted_to'] = $source_entity->label();
  }

  /** @var \Drupal\yamlform\YamlFormSubmissionViewBuilder $view_builder */
  $view_builder = \Drupal::entityManager()->getViewBuilder('yamlform_submission');

  $yamlform = $yamlform_submission->getYamlForm();
  $data = $yamlform_submission->getData();
  $inputs = $yamlform->getInputs();
  $variables['data'] = $view_builder->buildInputs($inputs, $data, [], 'text');
}

/**
 * Prepares variables for YAML form submission YAML template.
 *
 * Default template: yamlform-submission-yaml.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A YAML form submission.
 */
function template_preprocess_yamlform_submission_yaml(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];

  $data = $yamlform_submission->toArray();
  foreach ($data as $key => $item) {
    $value = reset($item);
    $data[$key] = reset($value);
  }
  $yaml = Yaml::encode($data);
  $yaml = YamlFormTidy::tidy($yaml);
  // Remove the pipe (|) after data: so that the 'data' is considered YAML and
  // not a string of text.
  $yaml = preg_replace('/^data: \|/m', 'data:', $yaml);
  $variables['yaml'] = $yaml;
}

/**
 * Prepares variables for YAML form CodeMirror template.
 *
 * Default template: yamlform-codemirror.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - code: The code.
 *   - type: The type of code.
 */
function template_preprocess_yamlform_codemirror(array &$variables) {
  $variables['mode'] = YamlFormCodeMirrorBase::getMode($variables['type']);
}

/**
 * Prepares variables for YAML form element base HTML template.
 *
 * Default template: yamlform-element-base-html.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - element: The YAML form element.
 *   - value: The content for the element.
 *   - options Associative array of options for element.
 *     - multiline: Flag to determine if value spans multiple lines.
 *     - email: Flag to determine if element is for an email.
 */
function template_preprocess_yamlform_element_base_html(array &$variables) {
  $element = $variables['element'];
  $variables['title'] = (isset($element['#title'])) ? $element['#title'] : NULL;
}

/**
 * Prepares variables for YAML form element base text template.
 *
 * Default template: yamlform-element-base-text.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - element: The YAML form element.
 *   - value: The content for the element.
 *   - options Associative array of options for element.
 *     - multiline: Flag to determine if value spans multiple lines.
 *     - email: Flag to determine if element is for an email.
 */
function template_preprocess_yamlform_element_base_text(array &$variables) {
  $element = $variables['element'];
  $variables['title'] = (isset($element['#title'])) ? $element['#title'] : NULL;
}

/**
 * Prepares variables for YAML form container base HTML template.
 *
 * Default template: yamlform-container-base-html.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - element: The YAML form element.
 *   - value: The content for the element.
 *   - options Associative array of options for element.
 *     - multiline: Flag to determine if value spans multiple lines.
 *     - email: Flag to determine if element is for an email.
 */
function template_preprocess_yamlform_container_base_html(array &$variables) {
  $element = $variables['element'];
  $variables['title'] = (isset($element['#title'])) ? $element['#title'] : NULL;
}

/**
 * Prepares variables for YAML form container base text template.
 *
 * Default template: yamlform-container-base-text.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - element: The YAML form element.
 *   - value: The content for the element.
 *   - options Associative array of options for element.
 *     - multiline: Flag to determine if value spans multiple lines.
 *     - email: Flag to determine if element is for an email.
 */
function template_preprocess_yamlform_container_base_text(array &$variables) {
  $element = $variables['element'];
  $variables['title'] = (isset($element['#title'])) ? $element['#title'] : NULL;
}
