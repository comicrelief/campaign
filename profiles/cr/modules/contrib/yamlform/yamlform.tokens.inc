<?php

/**
 * @file
 * Builds placeholder replacement tokens for YAML forms and submission.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Url;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Utility\YamlFormDateHelper;

/**
 * Implements hook_token_info().
 */
function yamlform_token_info() {
  $types['yamlform-submission'] = [
    'name' => t('YAML form submissions'),
    'description' => t('Tokens related to YAML form submissions.'),
    'needs-data' => 'yamlform-submission',
  ];
  $types['yamlform'] = [
    'name' => t('YAML forms'),
    'description' => t('Tokens related to YAML forms.'),
    'needs-data' => 'yamlform',
  ];

  // YAML form submission related variables.
  $yamlform_submission['sid'] = [
    'name' => t('YAML form submission ID'),
    'description' => t('The ID of the YAML form submission .'),
  ];
  $yamlform_submission['uuid'] = [
    'name' => t('UUID'),
    'description' => t('The UUID of the YAML form submission.'),
  ];
  $yamlform_submission['token'] = [
    'name' => t('Token'),
    'description' => t('A secure token used to look up a submission.'),
  ];
  $yamlform_submission['ip-address'] = [
    'name' => t('IP address'),
    'description' => t('The IP address that was used when submitting the YAML form submission.'),
  ];
  $yamlform_submission['source-url'] = [
    'name' => t('Source URL'),
    'description' => t('The URL the user submitted the YAML form submission.'),
  ];
  $yamlform_submission['url'] = [
    'name' => t('URL'),
    'description' => t("The URL of the YAML form submission. Replace the '?' with the link template. Defaults to 'canonical' which displays the submission's data."),
    'dynamic' => TRUE,
  ];
  $yamlform_submission['user'] = [
    'name' => t('Submitter'),
    'description' => t('The user that submitted the YAML form submission.'),
    'type' => 'user',
  ];
  $yamlform_submission['langcode'] = [
    'name' => t('Langcode'),
    'description' => t('The language code of the YAML form submission.'),
    'type' => 'user',
  ];
  $yamlform_submission['language'] = [
    'name' => t('Language'),
    'description' => t('The language name of the the YAML form submission.'),
    'type' => 'user',
  ];
  $yamlform_submission['created'] = [
    'name' => t('Date created'),
    'description' => t('The date the YAML form submission was created.'),
    'type' => 'date',
  ];
  $yamlform_submission['completed'] = [
    'name' => t('Date completed'),
    'description' => t('The date the YAML form submission was completed.'),
    'type' => 'date',
  ];
  $yamlform_submission['changed'] = [
    'name' => t('Date changed'),
    'description' => t('The date the YAML form submission was most recently updated.'),
    'type' => 'date',
  ];
  $yamlform_submission['values'] = [
    'name' => t('YAML form submission values'),
    'description' => t("YAML form tokens from submitted data. Replace the '?' with the '{input_name}' or '{input_name}:{format}'. The {format} can be 'value', 'raw', or custom format specifically associated with the <a href=\":href\">element.</a>", [':href' => Url::fromRoute('yamlform.element_plugins')]),
    'dynamic' => TRUE,
  ];

  // YAML form related variables.
  $yamlform['id'] = [
    'name' => t('YAML form ID'),
    'description' => t('The ID of the YAML form.'),
  ];
  $yamlform['title'] = [
    'name' => t('title'),
    'description' => t('The title of the YAML form.'),
  ];

  $yamlform['description'] = [
    'name' => t('Description'),
    'description' => t('The administrative description of the YAML form.'),
  ];
  $yamlform['url'] = [
    'name' => t('URL'),
    'description' => t("The URL of the YAML form. Replace the '?' with the link template. Defaults to 'canonical' which displays the submission form."),
    'dynamic' => TRUE,
  ];

  // Chained tokens for YAML form submissions.
  $yamlform_submission['yamlform'] = [
    'name' => t('YAML form'),
    'description' => t('The YAML form that the YAML form submission belongs to.'),
    'type' => 'yamlform',
  ];
  $yamlform_submission['source-entity'] = [
    'name' => t('Source entity'),
    'description' => t('The entity that the YAML form submission was submitted from.'),
    'type' => 'entity',
  ];

  return [
    'types' => $types,
    'tokens' => [
      'yamlform-submission' => $yamlform_submission,
      'yamlform' => $yamlform,
    ],
  ];
}

/**
 * Implements hook_tokens().
 */
function yamlform_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();

  $url_options = ['absolute' => TRUE];
  if (isset($options['langcode'])) {
    $url_options['language'] = \Drupal::languageManager()->getLanguage($options['langcode']);
    $langcode = $options['langcode'];
  }
  else {
    $langcode = NULL;
  }

  $replacements = [];

  if ($type == 'yamlform-submission' && !empty($data['yamlform-submission'])) {

    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $data['yamlform-submission'];

    /** @var \Drupal\yamlform\YamlFormSubmissionViewBuilder $view_builder */
    $view_builder = \Drupal::entityManager()->getViewBuilder('yamlform_submission');

    /** @var \Drupal\yamlform\YamlFormElementManager $element_manager */
    $element_manager = \Drupal::service('plugin.manager.yamlform.element');

    $form_inputs = $yamlform_submission->getYamlForm()->getFlattenedInputs();
    $submission_data = $yamlform_submission->getData();

    $submission_options = isset($data['yamlform-submission-options']) ? $data['yamlform-submission-options'] : [];

    foreach ($tokens as $name => $original) {
      if (strpos($name, 'values:') === 0) {
        // Parse [values:{name}:{format}] token with optional format.
        $keys = explode(':', $name);
        $key = $keys[1];
        $format = (isset($keys[2])) ? $keys[2] : NULL;
        if (isset($submission_data[$key]) && isset($form_inputs[$key])) {
          $element = $form_inputs[$key];
          // Apply $format to the element.
          if (isset($format)) {
            $element['#format'] = $format;
          }
          $format_method = (empty($submission_options['html'])) ? 'formatText' : 'formatHtml';
          $value = $element_manager->invokeMethod($format_method, $element, $submission_data[$key], $submission_options);
          // Note, tokens can't include CSS and JS libraries since they will
          // can be included in an email.
          $replacements[$original] = (is_array($value)) ? \Drupal::service('renderer')->renderPlain($value) : (string) $value;
        }
      }
      elseif (strpos($name, 'url:') === 0) {
        $key = str_replace('url:', '', $name);
        if ($yamlform_submission->id() && $yamlform_submission->hasLinkTemplate($key)) {
          $replacements[$original] = $yamlform_submission->toUrl($key, $url_options)->toString();
        }
      }
      else {
        switch ($name) {
          case 'created':
          case 'completed':
          case 'changed':
            $replacements[$original] = YamlFormDateHelper::format($yamlform_submission->{$name}->value, 'medium', '', NULL, $langcode);
            break;

          case 'langcode':
          case 'sid':
          case 'uuid':
            $replacements[$original] = $yamlform_submission->{$name}->value;
            break;

          case 'ip-address':
            $replacements[$original] = $yamlform_submission->remote_addr->value;
            break;

          case 'in_draft':
            $replacements[$original] = $yamlform_submission->in_draft->value ? t('Yes') : t('No');
            break;

          case 'language':
            $replacements[$original] = \Drupal::languageManager()->getLanguage($yamlform_submission->langcode->value)->getName();
            break;

          case 'source-url':
            $replacements[$original] = $yamlform_submission->getSourceUrl()->toString();
            break;

          case 'source-entity':
            $source_entity = $yamlform_submission->getSourceEntity();
            $replacements[$original] = ($source_entity) ? $source_entity->label() : '';
            break;

          case 'token':
            $replacements[$original] = $yamlform_submission->getToken();
            break;

          case 'url':
            if ($yamlform_submission->id()) {
              $replacements[$original] = $yamlform_submission->toUrl('canonical', $url_options)->toString();
            }
            break;

          case 'user':
            /** @var \Drupal\Core\Session\AccountInterface $account */
            $account = $yamlform_submission->uid->entity;
            $replacements[$original] = $account->getDisplayName();
            break;

          case 'values':
            $submission_format = (!empty($submission_options['html'])) ? 'html' : 'text';
            $build = $view_builder->buildInputs(
              $yamlform_submission->getYamlForm()->getInputs(),
              $submission_data,
              $submission_options,
              $submission_format
            );
            // Note, tokens can't include CSS and JS libraries since they will
            // can be included in an email.
            $replacements[$original] = \Drupal::service('renderer')->renderPlain($build);
            break;

          case 'yamlform':
            $yamlform = $yamlform_submission->getYamlForm();
            $bubbleable_metadata->addCacheableDependency($yamlform);
            $replacements[$original] = $yamlform->label();
            break;

        }
      }
    }

    // Chained token relationships.
    if ($yamlform_tokens = $token_service->findWithPrefix($tokens, 'yamlform')) {
      $yamlform = YamlForm::load($yamlform_submission->bundle());
      $replacements += $token_service->generate('yamlform', $yamlform_tokens, ['yamlform' => $yamlform], $options, $bubbleable_metadata);
    }
    if ($entity_tokens = $token_service->findWithPrefix($tokens, 'source-entity')) {
      $entity = $yamlform_submission->getSourceEntity();
      $replacements += $token_service->generate($entity->getEntityTypeId(), $entity_tokens, [$entity->getEntityTypeId() => $entity], $options, $bubbleable_metadata);
    }

  }
  elseif ($type == 'yamlform' && !empty($data['yamlform'])) {

    $yamlform = $data['yamlform'];
    foreach ($tokens as $name => $original) {
      if (strpos($name, 'url:') === 0) {
        $key = str_replace('url:', '', $name);
        if ($yamlform->hasLinkTemplate($key)) {
          $replacements[$original] = $yamlform->toUrl($key, $url_options)->toString();
        }
      }
      else {
        switch ($name) {
          case 'id':
            $replacements[$original] = $yamlform->id();
            break;

          case 'title':
            $replacements[$original] = $yamlform->label();
            break;

          case 'description':
            $replacements[$original] = $yamlform->getDescription();
            break;

          case 'url':
            $replacements[$original] = $yamlform->toUrl('canonical', $url_options)->toString();
            break;
        }
      }
    }
  }

  return $replacements;
}
