<?php

namespace Drupal\yamlform;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

/**
 * Base for controller for form settings.
 */
class YamlFormEntitySettingsForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->entity;

    $default_settings = $this->config('yamlform.settings')->get('settings');
    $settings = $yamlform->getSettings();

    // General.
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General settings'),
      '#open' => TRUE,
    ];
    $form['general']['id'] = [
      '#type' => 'item',
      '#title' => $this->t('ID'),
      '#markup' => $yamlform->id(),
      '#value' => $yamlform->id(),
    ];
    $form['general']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#default_value' => $yamlform->label(),
      '#required' => TRUE,
      '#id' => 'title',
    ];
    $form['general']['description'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Administrative description'),
      '#default_value' => $yamlform->get('description'),
      '#rows' => 2,
    ];
    $form['general']['template'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow this form to be used as a template.'),
      '#description' => $this->t('If checked, this form will be available as template, which can be duplicated, to all users who can create new forms.'),
      '#access' => $this->moduleHandler->moduleExists('yamlform_templates'),
      '#default_value' => $yamlform->isTemplate(),
    ];
    $form['general']['results_disabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable saving of submissions.'),
      '#return_value' => TRUE,
      '#description' => $this->t('If saving of submissions is disabled, submission settings, submission limits and the saving of drafts will be disabled.  Submissions must be sent via an email or handled using a custom <a href=":href">form handler</a>.', [':href' => Url::fromRoute('entity.yamlform.handlers_form', ['yamlform' => $yamlform->id()])->toString()]),
      '#default_value' => $settings['results_disabled'],
    ];

    // Page.
    $form['page'] = [
      '#type' => 'details',
      '#title' => $this->t('Page settings'),
      '#open' => TRUE,
    ];
    $default_page_submit_path = trim($default_settings['default_page_base_path'], '/') . '/' . str_replace('_', '-', $yamlform->id());
    $t_args = [
      ':node_href' => Url::fromRoute('node.add', ['node_type' => 'yamlform'])->toString(),
      ':block_href' => Url::fromRoute('block.admin_display')->toString(),
    ];
    $default_settings['default_page_submit_path'] = $default_page_submit_path;
    $default_settings['default_page_confirm_path'] = $default_page_submit_path . '/confirmation';
    $form['page']['page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow users to post submission from a dedicated URL.'),
      '#description' => $this->t('If unchecked this form must be attached to a <a href=":node_href">node</a> or a <a href=":block_href">block</a> to receive submissions.', $t_args),
      '#default_value' => $settings['page'],
    ];
    if ($this->moduleHandler->moduleExists('path')) {
      $form['page']['page_submit_path'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Submit URL alias'),
        '#description' => $this->t('Optionally specify an alternative URL by which the form submit page can be accessed.', $t_args),
        '#default_value' => $settings['page_submit_path'],
        '#states' => [
          'visible' => [
            ':input[name="page"]' => ['checked' => TRUE],
          ],
        ],
      ];
      $form['page']['page_confirm_path'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Confirm  URL alias'),
        '#description' => $this->t('Optionally specify an alternative URL by which the form confirmation page(after the form has been submitted) can be accessed.', $t_args),
        '#default_value' => $settings['page_confirm_path'],
        '#states' => [
          'visible' => [
            ':input[name="page"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    // Form.
    $form['form'] = [
      '#type' => 'details',
      '#title' => $this->t('Form settings'),
      '#open' => TRUE,
    ];
    $form['form']['status'] = [
      '#type' => 'radios',
      '#title' => $this->t('Form status'),
      '#default_value' => ($yamlform->get('status') == 1) ? 1 : 0,
      '#description' => $this->t('Closing a form prevents any further submissions by any users, except submission administrators.'),
      '#options' => [
        1 => $this->t('Open'),
        0 => $this->t('Closed'),
      ],
      '#required' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="template"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['form']['form_closed_message']  = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Form closed message'),
      '#description' => $this->t('A message to be displayed notifying the user that the form is closed.'),
      '#default_value' => $settings['form_closed_message'],
    ];
    $form['form']['form_exception_message']  = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Form exception message'),
      '#description' => $this->t('A message to be displayed if the form breaks.'),
      '#default_value' => $settings['form_exception_message'],
    ];
    $form['form']['form_submit_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form submit button label'),
      '#size' => 20,
      '#default_value' => $settings['form_submit_label'],
    ];
    $form['form']['form_prepopulate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow elements to be populated using query string parameters.'),
      '#description' => $this->t("If checked, elements can be populated using query string parameters. For example, appending ?name=John+Smith to a form's URL would setting an the 'name' element's default value to 'John Smith'."),
      '#return_value' => TRUE,
      '#default_value' => $settings['form_prepopulate'],
    ];
    $form['form']['form_prepopulate_source_entity'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow source entity to be populated using query string parameters.'),
      '#description' => $this->t("If checked, source entity can be populated using query string parameters. For example, appending ?source_entity_type=user&source_entity_id=1 to a form's URL would set a submission's 'Submitted to' value to '@user.", ['@user' => User::load(1)->label()]),
      '#return_value' => TRUE,
      '#default_value' => $settings['form_prepopulate_source_entity'],
    ];
    if ($default_settings['default_form_novalidate']) {
      $form['form']['form_novalidate_disabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Disable client-side validation'),
        '#description' => $this->t('Client-side validation is disabled for all forms.'),
        '#disabled' => TRUE,
        '#default_value' => TRUE,
      ];
      $form['form']['form_novalidate'] = [
        '#type' => 'value',
        '#value' => $settings['form_novalidate'],
      ];
    }
    else {
      $form['form']['form_novalidate'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Disable client-side validation'),
        '#description' => $this->t('If checked, the <a href="@href">novalidate</a> attribute, which disables client-side validation, will be added to this forms.', ['@href' => 'http://www.w3schools.com/tags/att_form_novalidate.asp']),
        '#return_value' => TRUE,
        '#default_value' => $settings['form_novalidate'],
      ];
    }
    $form['form']['form_autofocus'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autofocus'),
      '#description' => $this->t('If checked, the first visible and enabled input will be focused for new submissions.'),
      '#return_value' => TRUE,
      '#default_value' => $settings['form_autofocus'],
    ];
    if ($default_settings['default_form_details_toggle']) {
      $form['form']['form_details_toggle_disabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Display collapse/expand all details link'),
        '#description' => $this->t('Expand/collapse all (details) is automatically added to all forms.'),
        '#disabled' => TRUE,
        '#default_value' => TRUE,
      ];
      $form['form']['form_details_toggle'] = [
        '#type' => 'value',
        '#value' => $settings['form_details_toggle'],
      ];
    }
    else {
      $form['form']['form_details_toggle'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Display collapse/expand all details link'),
        '#description' => $this->t('If checked, an expand/collapse all (details) link will be added to this forms when there are two or more details elements.'),
        '#return_value' => TRUE,
        '#default_value' => $settings['form_details_toggle'],
      ];
    }

    // Wizard.
    $form['wizard'] = [
      '#type' => 'details',
      '#title' => $this->t('Wizard settings'),
      '#open' => TRUE,
    ];
    $form['wizard']['wizard_progress_bar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show wizard progress bar'),
      '#return_value' => TRUE,
      '#default_value' => $settings['wizard_progress_bar'],
    ];
    $form['wizard']['wizard_progress_pages'] = [
      '#type' => 'checkbox',
      '#return_value' => TRUE,
      '#title' => $this->t('Show wizard progress pages'),
      '#default_value' => $settings['wizard_progress_pages'],
    ];
    $form['wizard']['wizard_progress_percentage'] = [
      '#type' => 'checkbox',
      '#return_value' => TRUE,
      '#title' => $this->t('Show wizard progress percentage'),
      '#default_value' => $settings['wizard_progress_percentage'],
    ];
    $form['wizard']['wizard_prev_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Previous wizard page button label'),
      '#description' => $this->t('This is used for the previous page button within a wizard.'),
      '#size' => 20,
      '#default_value' => $settings['wizard_prev_button_label'],
    ];
    $form['wizard']['wizard_next_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Next wizard page button label'),
      '#description' => $this->t('This is used for the next page button within a wizard.'),
      '#size' => 20,
      '#default_value' => $settings['wizard_next_button_label'],
    ];
    $form['wizard']['wizard_complete'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include confirmation page in progress'),
      '#return_value' => TRUE,
      '#default_value' => $settings['wizard_complete'],
    ];
    $form['wizard']['wizard_start_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wizard start label'),
      '#size' => 20,
      '#default_value' => $settings['wizard_start_label'],
    ];
    $form['wizard']['wizard_complete_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wizard end label'),
      '#size' => 20,
      '#default_value' => $settings['wizard_complete_label'],
      '#states' => [
        'visible' => [
          ':input[name="wizard_complete"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Preview.
    $form['preview'] = [
      '#type' => 'details',
      '#title' => $this->t('Preview settings'),
      '#open' => TRUE,
    ];
    $form['preview']['preview'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable preview page'),
      '#options' => [
        DRUPAL_DISABLED => $this->t('Disabled'),
        DRUPAL_OPTIONAL => $this->t('Optional'),
        DRUPAL_REQUIRED => $this->t('Required'),
      ],
      '#description' => $this->t('Add a page for previewing the form before submitting.'),
      '#default_value' => $settings['preview'],
    ];
    $form['preview']['settings'] = [
      '#type' => 'container',
      '#states' => [
        'invisible' => [
          ':input[name="preview"]' => ['value' => DRUPAL_DISABLED],
        ],
      ],
    ];
    $form['preview']['settings']['preview_next_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Preview button label'),
      '#description' => $this->t('The text for the button that will proceed to the preview page.'),
      '#size' => 20,
      '#default_value' => $settings['preview_next_button_label'],
    ];
    $form['preview']['settings']['preview_prev_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Previous page button label'),
      '#description' => $this->t('The text for the button to go backwards from the preview page.'),
      '#size' => 20,
      '#default_value' => $settings['preview_prev_button_label'],
    ];
    $form['preview']['settings']['preview_message'] = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Preview message'),
      '#description' => $this->t('A message to be displayed on the preview page.'),
      '#default_value' => $settings['preview_message'],
    ];

    // Draft.
    $form['draft'] = [
      '#type' => 'details',
      '#title' => $this->t('Draft settings'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="results_disabled"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['draft']['draft'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow your users to save and finish the form later.'),
      "#description" => $this->t('This option is available only for authenticated users.'),
      '#return_value' => TRUE,
      '#default_value' => $settings['draft'],
    ];
    $form['draft']['settings'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          ':input[name="draft"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['draft']['settings']['draft_auto_save'] = [
      '#type' => 'checkbox',
      '#return_value' => TRUE,
      '#title' => $this->t('Automatically save as draft when paging, previewing, and when there are validation errors.'),
      "#description" => $this->t('Automatically save partial submissions when users click the "Preview" button or when validation errors prevent form submission.'),
      '#default_value' => $settings['draft_auto_save'],
    ];
    $form['draft']['settings']['draft_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Draft button label'),
      '#description' => $this->t('The text for the button that will save a draft.'),
      '#size' => 20,
      '#default_value' => $settings['draft_button_label'],
    ];
    $form['draft']['settings']['draft_saved_message'] = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Draft saved message'),
      '#description' => $this->t('Message to be displayed when a draft is saved.'),
      '#default_value' => $settings['draft_saved_message'],
    ];
    $form['draft']['settings']['draft_loaded_message'] = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Draft loaded message'),
      '#description' => $this->t('Message to be displayed when a draft is loaded.'),
      '#default_value' => $settings['draft_loaded_message'],
    ];

    // Submission.
    $form['submission'] = [
      '#type' => 'details',
      '#title' => $this->t('Submission settings'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="results_disabled"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['submission']['form_confidential'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Confidential submissions'),
      '#description' => $this->t('Confidential submissions have no recorded IP address and must be submitted while logged out.'),
      '#return_value' => TRUE,
      '#default_value' => $settings['form_confidential'],
    ];
    $form['submission']['form_confidential_message']  = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Form confidential message'),
      '#description' => $this->t('A message to be displayed when authenticated users try to access a confidential form.'),
      '#default_value' => $settings['form_confidential_message'],
      '#states' => [
        'visible' => [
          ':input[name="form_confidential"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['submission']['token_update'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow users to update a submission using a secure token.'),
      '#description' => $this->t("If checked users will be able to update a submission using the form's URL appended with the submission's (secure) token.  The URL to update a submission while be available when viewing a submission's information and can be inserted into the an email using the [yamlform-submission:update-url] token."),
      '#return_value' => TRUE,
      '#default_value' => $settings['token_update'],
    ];
    $form['submission']['next_serial'] = [
      '#type' => 'number',
      '#title' => $this->t('Next submission number'),
      '#description' => $this->t('The value of the next submission number. This is usually 1 when you start and will go up with each form submission.'),
      '#min' => 1,
      '#default_value' => $yamlform->getState('next_serial') ?: 1,
    ];

    // Limits.
    $form['limits'] = [
      '#type' => 'details',
      '#title' => $this->t('Submission limits'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="results_disabled"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['limits']['limit_total'] = [
      '#type' => 'number',
      '#title' => $this->t('Total submissions limit'),
      '#min' => 1,
      '#default_value' => $settings['limit_total'],
    ];
    $form['limits']['entity_limit_total'] = [
      '#type' => 'number',
      '#title' => $this->t('Total submissions limit per entity'),
      '#min' => 1,
      '#default_value' => $settings['entity_limit_total'],
    ];
    $form['limits']['limit_total_message'] = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Total submissions limit message'),
      '#min' => 1,
      '#default_value' => $settings['limit_total_message'],
    ];
    $form['limits']['limit_user'] = [
      '#type' => 'number',
      '#title' => $this->t('Per user submission limit'),
      '#min' => 1,
      '#default_value' => $settings['limit_user'],
    ];
    $form['limits']['entity_limit_user'] = [
      '#type' => 'number',
      '#min' => 1,
      '#title' => $this->t('Per user submission limit per entity'),
      '#default_value' => $settings['entity_limit_user'],
    ];
    $form['limits']['limit_user_message'] = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Per user submission limit message'),
      '#default_value' => $settings['limit_user_message'],
    ];

    // Confirmation.
    $form['confirmation'] = [
      '#type' => 'details',
      '#title' => $this->t('Confirmation settings'),
      '#open' => TRUE,
    ];
    $form['confirmation']['confirmation_type'] = [
      '#title' => $this->t('Confirmation type'),
      '#type' => 'radios',
      '#options' => [
        'page' => $this->t('Page (redirects to new page and displays the confirmation message)'),
        'inline' => $this->t('Inline (reloads the current page and replaces the form with the confirmation message.)'),
        'message' => $this->t('Message (reloads the current page/form and displays the confirmation message at the top of the page.)'),
        'url' => $this->t('URL (redirects to a custom path or URL)'),
        'url_message' => $this->t('URL with message (redirects to a custom path or URL and displays the confirmation message at the top of the page.)'),
      ],
      '#default_value' => $settings['confirmation_type'],
    ];
    $form['confirmation']['confirmation_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Confirmation URL'),
      '#description' => $this->t('URL to redirect the user to upon successful submission.'),
      '#default_value' => $settings['confirmation_url'],
      '#states' => [
        'visible' => [
          [':input[name="confirmation_type"]' => ['value' => 'url']],
          'xor',
          [':input[name="confirmation_type"]' => ['value' => 'url_message']],
        ],
      ],
    ];
    $form['confirmation']['confirmation_message'] = [
      '#type' => 'yamlform_html_editor',
      '#title' => $this->t('Confirmation message'),
      '#description' => $this->t('Message to be shown upon successful submission.'),
      '#default_value' => $settings['confirmation_message'],
      '#states' => [
        'invisible' => [
          ':input[name="confirmation_type"]' => ['value' => 'url'],
        ],
      ],
    ];
    $form['confirmation']['token_tree_link'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['yamlform', 'yamlform-submission'],
      '#click_insert' => FALSE,
      '#dialog' => TRUE,
    ];

    // Author.
    $form['author'] = [
      '#type' => 'details',
      '#title' => $this->t('Author information'),
      '#open' => TRUE,
      '#access' => \Drupal::currentUser()->hasPermission('administer yamlform'),
    ];
    $form['author']['uid'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Authored by'),
      '#description' => $this->t("The username of the form author/owner."),
      '#target_type' => 'user',
      '#settings' => [
        'match_operator' => 'CONTAINS',
      ],
      '#selection_settings' => [
        'include_anonymous' => TRUE,
      ],
      '#default_value' => $yamlform->getOwner(),
    ];

    $this->appendDefaultValueToElementDescriptions($form, $default_settings);

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    unset($actions['delete']);
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    /** @var \Drupal\yamlform\YamlFormInterface $yamlform */
    $yamlform = $this->getEntity();

    /** @var \Drupal\yamlform\YamlFormSubmissionStorageInterface $submission_storage */
    $submission_storage = \Drupal::entityTypeManager()->getStorage('yamlform_submission');

    // Set next serial number.
    $next_serial = (int) $values['next_serial'];
    $max_serial = $submission_storage->getMaxSerial($yamlform);
    if ($next_serial < $max_serial) {
      drupal_set_message(t('The next submission number was increased to @min to make it higher than existing submissions.', ['@min' => $max_serial]));
      $next_serial = $max_serial;
    }
    $yamlform->setState('next_serial', $next_serial);

    // Remove main properties.
    unset(
      $values['id'],
      $values['title'],
      $values['description'],
      $values['template'],
      $values['status'],
      $values['uid'],
      $values['next_serial']
    );

    // Remove disabled properties.
    unset(
      $values['form_novalidate_disabled'],
      $values['form_details_toggle_disabled']
    );

    // Set settings and save the form.
    $yamlform->setSettings($values)->save();

    $this->logger('yamlform')->notice('Form settings @label saved.', ['@label' => $yamlform->label()]);
    drupal_set_message($this->t('Form settings %label saved.', ['%label' => $yamlform->label()]));
  }

  /**
   * Append default value to an element's description.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $default_settings
   *   An associative array container default yamlform settings.
   */
  protected function appendDefaultValueToElementDescriptions(array &$form, array $default_settings) {
    foreach ($form as $key => &$element) {
      // Skip if not a FAPI element.
      if (Element::property($key) || !is_array($element)) {
        continue;
      }

      if (isset($element['#type']) && !empty($default_settings["default_$key"]) && empty($element['#disabled'])) {
        if (!isset($element['#description'])) {
          $element['#description'] = '';
        }
        $element['#description'] .= ($element['#description'] ? '<br/>' : '');
        $element['#description'] .= $this->t('Defaults to: %value', ['%value' => $default_settings["default_$key"]]);
      }

      $this->appendDefaultValueToElementDescriptions($element, $default_settings);
    }
  }

}
