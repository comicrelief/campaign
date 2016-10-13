<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

use Drupal\Core\Link;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Xss;
use Drupal\yamlform\Element\YamlFormCodeMirror;
use Drupal\yamlform\Utility\YamlFormTidy;
use Drupal\yamlform\Utility\YamlFormDateHelper;
use Drupal\yamlform\Utility\YamlFormDialogHelper;
use Drupal\yamlform\Utility\YamlFormElementHelper;

/**
 * Prepares variables for yamlform help.
 *
 * Default template: yamlform_help.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - title: Help title.
 *   - content: Help content.
 */
function template_preprocess_yamlform_help(&$variables) {
  $info = $variables['info'];

  $variables += $info;

  $help = [];
  if (is_array($info['content'])) {
    $help['content'] = $info['content'];
  }
  else {
    $help['content'] = [
      '#markup' => $info['content'],
    ];
  }

  if ($info['youtube_id']) {
    $video_display = \Drupal::config('yamlform.settings')->get('ui.video_display');
    $classes = ['button', 'button-action', 'button--small', 'button-yamlform-play'];
    switch ($video_display) {
      case 'dialog';
        $help['link'] = [
          '#type' => 'link',
          '#title' => 'Watch video',
          '#url' => Url::fromRoute('yamlform.help', ['id' => str_replace('_', '-', $info['id'])]),
          '#attributes' => YamlFormDialogHelper::getModalDialogAttributes(1000, $classes),
          '#prefix' => ' ',
        ];
        break;

      case 'link':
        $help['link'] = [
          '#type' => 'link',
          '#title' => 'Watch video',
          '#url' => Url::fromUri('https://youtu.be/' . $info['youtube_id']),
          '#attributes' => ['class' => $classes],
          '#prefix' => ' ',
        ];

        break;

      case 'hidden':
      default:
        break;
    }
  }

  $variables['help'] = $help;
}

/**
 * Prepares variables for yamlform templates.
 *
 * Default template: yamlform.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #action, #method, #attributes, #yamlform_children.
 */
function template_preprocess_yamlform(&$variables) {
  template_preprocess_form($variables);
}

/**
 * Prepares variables for form submission navigation templates.
 *
 * Default template: yamlform-submission-navigation.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A form submission.
 *   - rel: Form submission link template.
 *          (canonical, edit-form, resend-form, html, text, or yaml).
 */
function template_preprocess_yamlform_submission_navigation(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormRequestInterface $request_handler */
  $request_handler = \Drupal::service('yamlform.request');
  /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $yamlform_submission_storage */
  $yamlform_submission_storage = \Drupal::entityTypeManager()->getStorage('yamlform_submission');

  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];

  // Get the route name, parameters, and source entity for the current page.
  // This ensures that the user stays within their current context as they are
  // paging through submission.
  $route_name = \Drupal::routeMatch()->getRouteName();
  $route_parameters = \Drupal::routeMatch()->getRawParameters()->all();
  $source_entity = $request_handler->getCurrentSourceEntity(['yamlform_submission']);

  if (strpos(\Drupal::routeMatch()->getRouteName(), 'yamlform.user.submission') !== FALSE) {
    $account = \Drupal::currentUser();
  }
  else {
    $account = NULL;
  }

  if ($previous_submission = $yamlform_submission_storage->getPreviousSubmission($yamlform_submission, $source_entity, $account)) {
    $variables['prev_url'] = Url::fromRoute($route_name, ['yamlform_submission' => $previous_submission->id()] + $route_parameters)->toString();
  }
  if ($next_submission = $yamlform_submission_storage->getNextSubmission($yamlform_submission, $source_entity, $account)) {
    $variables['next_url'] = Url::fromRoute($route_name, ['yamlform_submission' => $next_submission->id()] + $route_parameters)->toString();
  }

  $variables['#attached']['library'][] = 'yamlform/yamlform.navigation';
}

/**
 * Prepares variables for form submission information template.
 *
 * Default template: yamlform-submission-information.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A form submission.
 */
function template_preprocess_yamlform_submission_information(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];
  $yamlform = $yamlform_submission->getYamlForm();

  $variables['yamlform_id'] = $yamlform->id();
  $variables['serial'] = $yamlform_submission->serial();
  $variables['sid'] = $yamlform_submission->id();
  $variables['uuid'] = $yamlform_submission->uuid();
  $variables['is_draft'] = $yamlform_submission->isDraft() ? t('Yes') : t('No');
  $variables['current_page'] = $yamlform_submission->getCurrentPageTitle();
  $variables['remote_addr'] = $yamlform_submission->getRemoteAddr();
  $variables['submitted_by'] = $yamlform_submission->getOwner()->toLink();
  $variables['form'] = $yamlform->toLink();
  $variables['created'] = YamlFormDateHelper::format($yamlform_submission->getCreatedTime());
  $variables['completed'] = YamlFormDateHelper::format($yamlform_submission->getCompletedTime());
  $variables['changed'] = YamlFormDateHelper::format($yamlform_submission->getChangedTime());
  $variables['sticky'] = $yamlform_submission->isSticky() ? t('Yes') : '';
  $variables['notes'] = $yamlform_submission->getNotes();

  // @see \Drupal\Core\Field\Plugin\Field\FieldFormatter\LanguageFormatter::viewValue()
  $languages = \Drupal::languageManager()->getNativeLanguages();
  $langcode = $yamlform_submission->get('langcode')->value;
  $variables['language'] = isset($languages[$langcode]) ? $languages[$langcode]->getName() : $langcode;

  if ($source_url = $yamlform_submission->getSourceUrl()) {
    $variables['uri'] = Link::fromTextAndUrl($source_url->setAbsolute(FALSE)->toString(), $source_url);;
  }

  if ($yamlform->getSetting('token_update')) {
    $token_url = $yamlform_submission->getTokenUrl();
    $variables['token_update'] = Link::fromTextAndUrl($token_url->setAbsolute(FALSE)->toString(), $token_url);
  }

  if ($source_entity = $yamlform_submission->getSourceEntity()) {
    $variables['submitted_to'] = $source_entity->toLink();
  }

  $variables['submissions_view'] = FALSE;
  if ($yamlform->access('submission_view_any')) {
    $variables['submissions_view'] = TRUE;
  }
  elseif ($source_entity) {
    $entity_type = $source_entity->getEntityTypeId();
    if (\Drupal::currentUser()->hasPermission("view yamlform node submissions any $entity_type")) {
      $variables['submissions_view'] = TRUE;
    }
    elseif (\Drupal::currentUser()->hasPermission("view yamlform node submissions own $entity_type")
      && method_exists($source_entity, 'getOwnerId')
      && $source_entity->getOwnerId() == \Drupal::currentUser()->id()
    ) {
      $variables['submissions_view'] = TRUE;
    }
  }

}

/**
 * Prepares variables for form submission HTML template.
 *
 * Default template: yamlform-submission-html.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A form submission.
 */
function template_preprocess_yamlform_submission_html(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];

  /** @var \Drupal\yamlform\YamlFormSubmissionViewBuilderInterface $view_builder */
  $view_builder = \Drupal::entityTypeManager()->getViewBuilder('yamlform_submission');

  $yamlform = $yamlform_submission->getYamlForm();
  $data = $yamlform_submission->getData();
  $elements = $yamlform->getElementsInitialized();
  $variables['data'] = $view_builder->buildElements($elements, $data);
}

/**
 * Prepares variables for form submission table template.
 *
 * Default template: yamlform-submission-table.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A form submission.
 */
function template_preprocess_yamlform_submission_table(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];

  /** @var \Drupal\yamlform\YamlFormSubmissionViewBuilderInterface $view_builder */
  $view_builder = \Drupal::entityTypeManager()->getViewBuilder('yamlform_submission');

  $yamlform = $yamlform_submission->getYamlForm();
  $data = $yamlform_submission->getData();
  $elements = $yamlform->getElementsFlattenedAndHasValue();

  $variables['table'] = $view_builder->buildTable($elements, $data);
}

/**
 * Prepares variables for form submission plain text template.
 *
 * Default template: yamlform-submission-text.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A form submission.
 */
function template_preprocess_yamlform_submission_text(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];

  $variables['sid'] = $yamlform_submission->id();
  $variables['uuid'] = $yamlform_submission->uuid();
  $variables['is_draft'] = $yamlform_submission->isDraft() ? t('Yes') : t('No');
  $variables['current_page'] = $yamlform_submission->getCurrentPage();
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

  /** @var \Drupal\yamlform\YamlFormSubmissionViewBuilderInterface $view_builder */
  $view_builder = \Drupal::entityTypeManager()->getViewBuilder('yamlform_submission');

  $yamlform = $yamlform_submission->getYamlForm();
  $data = $yamlform_submission->getData();
  $elements = $yamlform->getElementsInitialized();
  $variables['data'] = $view_builder->buildElements($elements, $data, [], 'text');
}

/**
 * Prepares variables for form submission YAML template.
 *
 * Default template: yamlform-submission-yaml.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform_submission: A form submission.
 */
function template_preprocess_yamlform_submission_yaml(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
  $yamlform_submission = $variables['yamlform_submission'];

  $data = $yamlform_submission->toArray();
  foreach ($data as $key => $item) {
    // Issue #2567899 It seems it is impossible to save an empty string to an entity.
    // @see https://www.drupal.org/node/2567899
    // Solution: Set empty (aka NULL) items to an empty string.
    if (empty($item)) {
      $data[$key] = '';
    }
    else {
      $value = reset($item);
      $data[$key] = reset($value);
    }
  }
  $data['data'] = $yamlform_submission->getData();
  $yaml = Yaml::encode($data);
  $yaml = YamlFormTidy::tidy($yaml);
  $variables['yaml'] = [
    '#markup' => $yaml,
    '#allowed_tags' => Xss::getAdminTagList(),
  ];;
}

/**
 * Prepares variables for form CodeMirror template.
 *
 * Default template: yamlform-codemirror.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - code: The code.
 *   - type: The type of code.
 */
function template_preprocess_yamlform_codemirror(array &$variables) {
  $variables['mode'] = YamlFormCodeMirror::getMode($variables['type']);
  if (is_string($variables['code'])) {
    $variables['code'] = [
      '#markup' => $variables['code'],
      '#allowed_tags' => Xss::getAdminTagList(),
    ];
  }
}

/**
 * Prepares variables for form element base HTML template.
 *
 * Default template: yamlform-element-base-html.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - element: The form element.
 *   - value: The content for the element.
 *   - options Associative array of options for element.
 *     - multiline: Flag to determine if value spans multiple lines.
 *     - email: Flag to determine if element is for an email.
 */
function template_preprocess_yamlform_element_base_html(array &$variables) {
  $element = $variables['element'];
  $variables['title'] = (YamlFormElementHelper::isTitleDisplayed($element)) ? $element['#title'] : NULL;
}

/**
 * Prepares variables for form element base text template.
 *
 * Default template: yamlform-element-base-text.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - element: The form element.
 *   - value: The content for the element.
 *   - options Associative array of options for element.
 *     - multiline: Flag to determine if value spans multiple lines.
 *     - email: Flag to determine if element is for an email.
 */
function template_preprocess_yamlform_element_base_text(array &$variables) {
  template_preprocess_yamlform_element_base_html($variables);
}

/**
 * Prepares variables for form container base HTML template.
 *
 * Default template: yamlform-container-base-html.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - element: The form element.
 *   - value: The content for the element.
 *   - options Associative array of options for element.
 *     - multiline: Flag to determine if value spans multiple lines.
 *     - email: Flag to determine if element is for an email.
 */
function template_preprocess_yamlform_container_base_html(array &$variables) {
  $element = $variables['element'];
  $variables['title'] = (isset($element['#title'])) ? $element['#title'] : NULL;
  $variables['id'] = $element['#yamlform_id'];
}

/**
 * Prepares variables for form container base text template.
 *
 * Default template: yamlform-container-base-text.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - element: The form element.
 *   - value: The content for the element.
 *   - options Associative array of options for element.
 *     - multiline: Flag to determine if value spans multiple lines.
 *     - email: Flag to determine if element is for an email.
 */
function template_preprocess_yamlform_container_base_text(array &$variables) {
  $element = $variables['element'];
  $variables['title'] = (isset($element['#title'])) ? $element['#title'] : NULL;
}

/**
 * Prepares variables for form 'wizard' progress template.
 *
 * Default template: yamlform-progress.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform: A form.
 *   - current_page: The current wizard page.
 */
function template_preprocess_yamlform_progress(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
  $yamlform = $variables['yamlform'];

  $pages = $yamlform->getPages();

  $total = count($pages);
  $current_page = $variables['current_page'];
  // Jump to last page if 'current_page' is complete.
  if ($current_page === 'complete') {
    $current_page = $total - 1;
  }

  if ($yamlform->getSetting('wizard_progress_bar')) {
    $variables['bar'] = [
      '#theme' => 'yamlform_progress_bar',
      '#yamlform' => $variables['yamlform'],
      '#current_page' => $variables['current_page'],
    ];
  }

  if ($yamlform->getSetting('wizard_progress_pages')) {
    $variables['pages'] = t('Page @start of @end', ['@start' => $current_page + 1, '@end' => $total]);
  }

  if ($yamlform->getSetting('wizard_progress_percentage')) {
    $variables['percentage'] = number_format(($current_page / ($total - 1)) * 100, 0) . '%';
  }
}

/**
 * Prepares variables for form 'wizard' progress bar template.
 *
 * Default template: yamlform-progress-bar.html.twig.
 *
 * @param array $variables
 *   An associative array containing the following key:
 *   - yamlform: A form.
 *   - current_page: The current wizard page.
 */
function template_preprocess_yamlform_progress_bar(array &$variables) {
  /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
  $yamlform = $variables['yamlform'];

  $pages = $yamlform->getPages();
  $variables['pages'] = [];
  foreach ($pages as $page) {
    $variables['pages'][] = (isset($page['#title'])) ? $page['#title'] : '';
  }

  // Jump to last page if 'current_page' is complete.
  if ($variables['current_page'] && $variables['current_page'] === 'complete') {
    $variables['current_page'] = count($variables['pages']) - 1;
  }
}

/**
 * Prepares variables for address composite element templates.
 *
 * Default template: yamlform-element-address.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #title, #value, #options, #description, #required,
 *     #attributes.
 */
function template_preprocess_yamlform_composite_address(&$variables) {
  $element = $variables['element'];
  foreach (Element::children($element) as $key) {
    if ($element[$key]['#access']) {
      $variables['content'][$key] = $element[$key];
    }
  }
  $variables['flexbox'] = (isset($element['#flexbox'])) ? $element['#flexbox'] : FALSE;
}

/**
 * Prepares variables for contact composite element templates.
 *
 * Default template: yamlform-element-contact.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #title, #value, #options, #description, #required,
 *     #attributes.
 */
function template_preprocess_yamlform_composite_contact(&$variables) {
  $element = $variables['element'];
  foreach (Element::children($element) as $key) {
    if ($element[$key]['#access']) {
      $variables['content'][$key] = $element[$key];
    }
  }
  $variables['flexbox'] = (isset($element['#flexbox'])) ? $element['#flexbox'] : FALSE;
}

/**
 * Prepares variables for creditcard composite element templates.
 *
 * Default template: yamlform-element-creditcard.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #title, #value, #options, #description, #required,
 *     #attributes.
 */
function template_preprocess_yamlform_composite_creditcard(&$variables) {
  $element = $variables['element'];
  foreach (Element::children($element) as $key) {
    if ($element[$key]['#access']) {
      $variables['content'][$key] = $element[$key];
    }
  }
  $variables['flexbox'] = (isset($element['#flexbox'])) ? $element['#flexbox'] : FALSE;
}

/**
 * Prepares variables for location composite element templates.
 *
 * Default template: yamlform-element-location.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #title, #value, #options, #description, #required,
 *     #attributes.
 */
function template_preprocess_yamlform_composite_location(&$variables) {
  $variables['content'] = $variables['element'];
}

/**
 * Prepares variables for name composite element templates.
 *
 * Default template: yamlform-element-name.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #title, #value, #options, #description, #required,
 *     #attributes.
 */
function template_preprocess_yamlform_composite_name(&$variables) {
  $element = $variables['element'];
  foreach (Element::children($element) as $key) {
    if ($element[$key]['#access']) {
      $variables['content'][$key] = $element[$key];
    }
  }
  $variables['flexbox'] = (isset($element['#flexbox'])) ? $element['#flexbox'] : FALSE;
}
