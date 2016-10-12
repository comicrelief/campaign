<?php

/**
 * @file
 * Builds placeholder replacement tokens for forms and submissions.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Utility\YamlFormDateHelper;

/**
 * Implements hook_token_info().
 */
function yamlform_token_info() {
  $types['yamlform-authenticated-user'] = [
    'name' => t('Form authenticated user'),
    'description' => t('Tokens related to the currently authenticated user.'),
    'type' => 'user',
  ];
  $types['yamlform-submission'] = [
    'name' => t('Form submissions'),
    'description' => t('Tokens related to form submissions.'),
    'needs-data' => 'yamlform-submission',
  ];
  $types['yamlform'] = [
    'name' => t('Forms'),
    'description' => t('Tokens related to forms.'),
    'needs-data' => 'yamlform',
  ];

  // Form submission related variables.
  $yamlform_submission['serial'] = [
    'name' => t('Form submission serial number'),
    'description' => t('The serial number of the form submission .'),
  ];
  $yamlform_submission['sid'] = [
    'name' => t('Form submission ID'),
    'description' => t('The ID of the form submission .'),
  ];
  $yamlform_submission['uuid'] = [
    'name' => t('UUID'),
    'description' => t('The UUID of the form submission.'),
  ];
  $yamlform_submission['token'] = [
    'name' => t('Token'),
    'description' => t('A secure token used to look up a submission.'),
  ];
  $yamlform_submission['ip-address'] = [
    'name' => t('IP address'),
    'description' => t('The IP address that was used when submitting the form submission.'),
  ];
  $yamlform_submission['source-url'] = [
    'name' => t('Source URL'),
    'description' => t('The URL the user submitted the form submission.'),
  ];
  $yamlform_submission['update-url'] = [
    'name' => t('Update URL'),
    'description' => t("The URL that can used to update the form submission. The form must be configurated to allow users to update a submission using a secure token."),
  ];
  $yamlform_submission['url'] = [
    'name' => t('URL'),
    'description' => t("The URL of the form submission. Replace the '?' with the link template. Defaults to 'canonical' which displays the submission's data."),
    'dynamic' => TRUE,
  ];
  $yamlform_submission['user'] = [
    'name' => t('Submitter'),
    'description' => t('The user that submitted the form submission.'),
    'type' => 'user',
  ];
  $yamlform_submission['langcode'] = [
    'name' => t('Langcode'),
    'description' => t('The language code of the form submission.'),
    'type' => 'user',
  ];
  $yamlform_submission['language'] = [
    'name' => t('Language'),
    'description' => t('The language name of the the form submission.'),
    'type' => 'user',
  ];
  $yamlform_submission['created'] = [
    'name' => t('Date created'),
    'description' => t('The date the form submission was created.'),
    'type' => 'date',
  ];
  $yamlform_submission['completed'] = [
    'name' => t('Date completed'),
    'description' => t('The date the form submission was completed.'),
    'type' => 'date',
  ];
  $yamlform_submission['changed'] = [
    'name' => t('Date changed'),
    'description' => t('The date the form submission was most recently updated.'),
    'type' => 'date',
  ];
  $yamlform_submission['values'] = [
    'name' => t('Form submission values'),
    'description' => t("Form tokens from submitted data. Replace the '?' with the '{element_name}' or '{element_name}:{format}'. The {format} can be 'value', 'raw', or custom format specifically associated with the <a href=\":href\">element.</a>", [':href' => Url::fromRoute('yamlform.element_plugins')]),
    'dynamic' => TRUE,
  ];

  // Form related variables.
  $yamlform['id'] = [
    'name' => t('Form ID'),
    'description' => t('The ID of the form.'),
  ];
  $yamlform['title'] = [
    'name' => t('title'),
    'description' => t('The title of the form.'),
  ];

  $yamlform['description'] = [
    'name' => t('Description'),
    'description' => t('The administrative description of the form.'),
  ];
  $yamlform['url'] = [
    'name' => t('URL'),
    'description' => t("The URL of the form. Replace the '?' with the link template. Defaults to 'canonical' which displays the submission form."),
    'dynamic' => TRUE,
  ];

  // Chained tokens for form submissions.
  $yamlform_submission['yamlform'] = [
    'name' => t('Form'),
    'description' => t('The form that the form submission belongs to.'),
    'type' => 'yamlform',
  ];
  $yamlform_submission['source-entity'] = [
    'name' => t('Source entity'),
    'description' => t("The (source) entity that the form submission was submitted from.  Use [yamlform-submission:source-entity] to get the source entity's label or [yamlform-submission:source-entity:{field_name|property}] retrieve a source entity's field or property value."),
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
  if ($type == 'yamlform-authenticated-user') {
    if (\Drupal::currentUser()->isAuthenticated()) {
      $account = User::load(\Drupal::currentUser()->id());
      $bubbleable_metadata->addCacheContexts(['user']);
      $replacements += $token_service->generate('user', $tokens, ['user' => $account], $options, $bubbleable_metadata);
    }
    else {
      foreach ($replacements as $name => $token) {
        $replacements[$name] = '';
      }
    }
  }
  elseif ($type == 'yamlform-submission' && !empty($data['yamlform-submission'])) {

    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $data['yamlform-submission'];

    /** @var \Drupal\yamlform\YamlFormSubmissionViewBuilderInterface $view_builder */
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('yamlform_submission');

    /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
    $element_manager = \Drupal::service('plugin.manager.yamlform.element');

    $form_elements = $yamlform_submission->getYamlForm()->getElementsInitializedAndFlattened();
    $submission_data = $yamlform_submission->getData();

    $submission_options = isset($data['yamlform-submission-options']) ? $data['yamlform-submission-options'] : [];

    foreach ($tokens as $name => $original) {
      if (strpos($name, 'values:') === 0) {
        // Parse [values:{name}:{format}] token with optional format.
        $keys = explode(':', $name);
        $key = $keys[1];
        $format = (isset($keys[2])) ? $keys[2] : NULL;
        if (isset($submission_data[$key]) && isset($form_elements[$key])) {
          $element = $form_elements[$key];
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
          case 'serial':
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

          case 'update-url':
            $replacements[$original] = $yamlform_submission->getTokenUrl()->toString();
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
            $build = $view_builder->buildElements(
              $yamlform_submission->getYamlForm()->getElementsInitialized(),
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
      if ($entity = $yamlform_submission->getSourceEntity()) {
        $replacements += $token_service->generate($entity->getEntityTypeId(), $entity_tokens, [$entity->getEntityTypeId() => $entity], $options, $bubbleable_metadata);
      }
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
