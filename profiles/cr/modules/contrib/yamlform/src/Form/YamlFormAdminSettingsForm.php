<?php

namespace Drupal\yamlform\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldType\FileItem;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Utility\YamlFormArrayHelper;
use Drupal\yamlform\YamlFormElementManagerInterface;
use Drupal\yamlform\YamlFormSubmissionExporterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure form admin settings for this site.
 */
class YamlFormAdminSettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * The form submission exporter.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionExporterInterface
   */
  protected $submissionExporter;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['yamlform.settings'];
  }

  /**
   * Constructs a YamlFormAdminSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $third_party_settings_manager
   *   The module handler.
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $element_manager
   *   The form element manager.
   * @param \Drupal\yamlform\YamlFormSubmissionExporterInterface $submission_exporter
   *   The form submission exporter.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $third_party_settings_manager, YamlFormElementManagerInterface $element_manager, YamlFormSubmissionExporterInterface $submission_exporter) {
    parent::__construct($config_factory);
    $this->moduleHandler = $third_party_settings_manager;
    $this->elementManager = $element_manager;
    $this->submissionExporter = $submission_exporter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('plugin.manager.yamlform.element'),
      $container->get('yamlform_submission.exporter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('yamlform.settings');
    $settings = $config->get('settings');

    $form['page'] = [
      '#type' => 'details',
      '#title' => $this->t('Page default settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['page']['default_page_base_path']  = [
      '#type' => 'textfield',
      '#title' => $this->t('Default base path for form URLs'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_page_base_path'),
    ];

    $form['form'] = [
      '#type' => 'details',
      '#title' => $this->t('Form default settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['form']['default_form_closed_message']  = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Default closed message'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_form_closed_message'),
    ];
    $form['form']['default_form_exception_message']  = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Default closed exception message'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_form_exception_message'),
    ];
    $form['form']['default_form_submit_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default submit button label'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $settings['default_form_submit_label'],
    ];
    $form['form']['default_form_confidential_message']  = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Default confidential message'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_form_confidential_message'),
    ];
    $form['form']['default_form_novalidate']  = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable client-side validation for all forms'),
      '#description' => $this->t('If checked, the <a href="@href">novalidate</a> attribute, which disables client-side validation, will be added to all forms.', ['@href' => 'http://www.w3schools.com/tags/att_form_novalidate.asp']),
      '#return_value' => TRUE,
      '#default_value' => $config->get('settings.default_form_novalidate'),
    ];
    $form['form']['default_form_details_toggle']  = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display collapse/expand all details link'),
      '#description' => $this->t('If checked, an expand/collapse all (details) link will be added to all forms with two or more details elements.'),
      '#return_value' => TRUE,
      '#default_value' => $config->get('settings.default_form_details_toggle'),
    ];

    $form['wizard'] = [
      '#type' => 'details',
      '#title' => $this->t('Wizard default settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['wizard']['default_wizard_prev_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default wizard previous page button label'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $settings['default_wizard_prev_button_label'],
    ];
    $form['wizard']['default_wizard_next_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default wizard next page button label'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $settings['default_wizard_next_button_label'],
    ];
    $form['wizard']['default_wizard_start_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default wizard start label'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $settings['default_wizard_start_label'],
    ];
    $form['wizard']['default_wizard_complete_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default wizard end label'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $settings['default_wizard_complete_label'],
    ];

    $form['preview'] = [
      '#type' => 'details',
      '#title' => $this->t('Preview default settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['preview']['default_preview_next_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default preview button label'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $settings['default_preview_next_button_label'],
    ];
    $form['preview']['default_preview_prev_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default preview previous page button label'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $settings['default_preview_prev_button_label'],
    ];
    $form['preview']['default_preview_message'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Default preview message'),
      '#required' => TRUE,
      '#default_value' => $settings['default_preview_message'],
    ];

    $form['draft'] = [
      '#type' => 'details',
      '#title' => $this->t('Draft default settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['draft']['default_draft_button_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default draft button label'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $settings['default_draft_button_label'],
    ];
    $form['draft']['default_draft_saved_message'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Default draft save message'),
      '#required' => TRUE,
      '#default_value' => $settings['default_draft_saved_message'],
    ];
    $form['draft']['default_draft_loaded_message'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Default draft load message'),
      '#required' => TRUE,
      '#default_value' => $settings['default_draft_loaded_message'],
    ];

    $form['confirmation'] = [
      '#type' => 'details',
      '#title' => $this->t('Confirmation default settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['confirmation']['default_confirmation_message']  = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Default confirmation message'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_confirmation_message'),
    ];

    $form['limit'] = [
      '#type' => 'details',
      '#title' => $this->t('Limit default settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['limit']['default_limit_total_message']  = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Default total submissions limit message'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_limit_total_message'),
    ];
    $form['limit']['default_limit_user_message']  = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Default per user submission limit message'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_limit_user_message'),
    ];

    // Elements.
    $form['elements'] = [
      '#type' => 'details',
      '#title' => $this->t('Elements default settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['elements']['allowed_tags'] = [
      '#type' => 'yamlform_radios_other',
      '#title' => $this->t('Allowed tags'),
      '#options' => [
        'admin' => $this->t('Admin tags Excludes: script, iframe, etc...'),
        'html' => $this->t('HTML tags: Includes only @html_tags.', ['@html_tags' => YamlFormArrayHelper::toString(Xss::getHtmlTagList())]),
      ],
      '#other__option_label' => $this->t('Custom tags'),
      '#other__placeholder' => $this->t('Enter multiple tags delimited using spaces'),
      '#required' => TRUE,
      '#description' => $this->t('Allowed tags are applied to an element propperty that may contain HTML. This includes element title, description, prefix, and suffix'),
      '#default_value' => $config->get('elements.allowed_tags'),
    ];
    $form['elements']['wrapper_classes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Wrapper CSS classes'),
      '#description' => $this->t('A list of classes that will be provided in the "Wrapper CSS classes" dropdown. Enter one or more classes on each line. These styles should be available in your theme\'s CSS file.'),
      '#required' => TRUE,
      '#default_value' => $config->get('elements.wrapper_classes'),
    ];
    $form['elements']['classes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Element CSS classes'),
      '#description' => $this->t('A list of classes that will be provided in the "Element CSS classes" dropdown. Enter one or more classes on each line. These styles should be available in your theme\'s CSS file.'),
      '#required' => TRUE,
      '#default_value' => $config->get('elements.classes'),
    ];
    $form['elements']['default_description_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Default description display'),
      '#options' => [
        '' => '',
        'before' => $this->t('Before'),
        'after' => $this->t('After'),
        'invisible' => $this->t('Invisible'),
        'tooltip' => $this->t('Tooltip'),
      ],
      '#description' => $this->t('Determines the default placement of the description for all form elements.'),
      '#default_value' => $config->get('elements.default_description_display'),
    ];
    $form['elements']['default_google_maps_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Maps API key'),
      '#description' => $this->t('Google requires users to use a valid API key. Using the <a href="https://console.developers.google.com/apis">Google API Manager</a>, you can enable the <em>Google Maps JavaScript API</em>. That will create (or reuse) a <em>Browser key</em> which you can paste here.'),
      '#default_value' => $config->get('elements.default_google_maps_api_key'),
    ];
    if ($this->moduleHandler->moduleExists('file')) {
      $form['elements']['default_max_filesize'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Default maximum upload size'),
        '#description' => $this->t('Enter a value like "512" (bytes), "80 KB" (kilobytes) or "50 MB" (megabytes) in order to restrict the allowed file size. If left empty the file sizes will be limited only by PHP\'s maximum post and file upload sizes (current limit <strong>%limit</strong>).', ['%limit' => format_size(file_upload_max_size())]),
        '#element_validate' => [[get_class($this), 'validateMaxFilesize']],
        '#size' => 10,
        '#default_value' => $config->get('elements.default_max_filesize'),
      ];
      $form['elements']['default_file_extensions'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Default allowed file extensions'),
        '#description' => $this->t('Separate extensions with a space and do not include the leading dot.'),
        '#element_validate' => [[get_class($this), 'validateExtensions']],
        '#required' => TRUE,
        '#maxlength' => 256,
        '#default_value' => $config->get('elements.default_file_extensions'),
      ];
    }

    // Format.
    $form['format'] = [
      '#type' => 'details',
      '#title' => $this->t('Format default settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $element_plugins = $this->elementManager->getInstances();
    foreach ($element_plugins as $element_id => $element_plugin) {
      $formats = $element_plugin->getFormats();
      // Make sure the element has formats.
      if (empty($formats)) {
        continue;
      }

      // Skip if the element just uses the default 'value' format.
      if (count($formats) == 1 && isset($formats['value'])) {
        continue;
      }

      // Append formats name to formats label.
      foreach ($formats as $format_name => $format_label) {
        $formats[$format_name] = new FormattableMarkup('@label (@name)', ['@label' => $format_label, '@name' => $format_name]);
      }

      // Create empty format since the select element is not required.
      // @see https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Select.php/class/Select/8
      $formats = ['' => '<' . $this->t('Default') . '>'] + $formats;

      $default_format = $element_plugin->getDefaultFormat();
      $default_format_label = (isset($formats[$default_format])) ? $formats[$default_format] : $default_format;
      $element_plugin_definition = $element_plugin->getPluginDefinition();
      $element_plugin_label = $element_plugin_definition['label'];

      $form['format'][$element_id] = [
        '#type' => 'select',
        '#title' => new FormattableMarkup('@label (@id)', ['@label' => $element_plugin_label, '@id' => str_replace('yamlform_', '', $element_id)]),
        '#description' => $this->t('Defaults to: %value', ['%value' => $default_format_label]),
        '#options' => $formats,
        '#default_value' => $config->get("format.$element_id"),
      ];
    }

    // Mail.
    $form['mail'] = [
      '#type' => 'details',
      '#title' => $this->t('Email default settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['mail']['default_from_mail']  = [
      '#type' => 'textfield',
      '#title' => $this->t('Default email from address'),
      '#required' => TRUE,
      '#default_value' => $config->get('mail.default_from_mail'),
    ];
    $form['mail']['default_from_name']  = [
      '#type' => 'textfield',
      '#title' => $this->t('Default email from name'),
      '#required' => TRUE,
      '#default_value' => $config->get('mail.default_from_name'),
    ];
    $form['mail']['default_subject']  = [
      '#type' => 'textfield',
      '#title' => $this->t('Default email subject'),
      '#required' => TRUE,
      '#default_value' => $config->get('mail.default_subject'),
    ];
    $form['mail']['default_body_text']  = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'text',
      '#title' => $this->t('Default email body (Plain text)'),
      '#required' => TRUE,
      '#default_value' => $config->get('mail.default_body_text'),
    ];
    $form['mail']['default_body_html']  = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'html',
      '#title' => $this->t('Default email body (HTML)'),
      '#required' => TRUE,
      '#default_value' => $config->get('mail.default_body_html'),
    ];
    $form['mail']['token_tree_link'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [
        'yamlform',
        'yamlform-submission',
      ],
      '#click_insert' => FALSE,
      '#dialog' => TRUE,
    ];

    // Export.
    $export_form_state = new FormState();
    $export_form_state->setValues($config->get('export') ?: []);
    $form['export'] = [
      '#type' => 'details',
      '#title' => $this->t('Export default settings'),
      '#open' => FALSE,
    ];
    $this->submissionExporter->buildForm($form, $export_form_state);

    // Batch.
    $form['batch'] = [
      '#type' => 'details',
      '#title' => $this->t('Batch settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['batch']['default_batch_export_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch export size'),
      '#min' => 1,
      '#required' => TRUE,
      '#default_value' => $config->get('batch.default_batch_export_size'),
    ];
    $form['batch']['default_batch_update_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch update size'),
      '#min' => 1,
      '#required' => TRUE,
      '#default_value' => $config->get('batch.default_batch_update_size'),
    ];
    $form['batch']['default_batch_delete_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch delete size'),
      '#min' => 1,
      '#required' => TRUE,
      '#default_value' => $config->get('batch.default_batch_delete_size'),
    ];

    // Test.
    $form['test'] = [
      '#type' => 'details',
      '#title' => $this->t('Test settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['test']['types'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
      '#title' => $this->t('Test data by element type'),
      '#description' => $this->t("Above test data is keyed by FAPI element #type."),
      '#default_value' => $config->get('test.types'),
    ];
    $form['test']['names'] = [
      '#type' => 'yamlform_codemirror',
      '#mode' => 'yaml',
      '#title' => $this->t('Test data by element name'),
      '#description' => $this->t("Above test data is keyed by full or partial element names. For example, Using 'zip' will populate fields that are named 'zip' and 'zip_code' but not 'zipcode' or 'zipline'."),
      '#default_value' => $config->get('test.names'),
    ];

    // UI.
    $form['ui'] = [
      '#type' => 'details',
      '#title' => $this->t('User interface settings'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];
    $form['ui']['video_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Video display'),
      '#description' => $this->t('Controls how videos are displayed in inline help and within the global help section.'),
      '#options' => [
        'dialog' => $this->t('Dialog'),
        'link' => $this->t('External link'),
        'hidden' => $this->t('Hidden'),
      ],
      '#default_value' => $config->get('ui.video_display'),
    ];
    $form['ui']['details_save'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Save details open/close state'),
      '#description' => $this->t('If checked, all <a href=":details_href">Details</a> element\'s open/close state will be saved using <a href=":local_storage_href">Local Storage</a>.', [
        ':details_href' => 'http://www.w3schools.com/tags/tag_details.asp',
        ':local_storage_href' => 'http://www.w3schools.com/html/html5_webstorage.asp',
      ]),
      '#return_value' => TRUE,
      '#default_value' => $config->get('ui.details_save'),
    ];
    $form['ui']['dialog_disabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable dialogs'),
      '#description' => $this->t('If checked, all modal dialogs (ie popups) will be disabled.'),
      '#return_value' => TRUE,
      '#default_value' => $config->get('ui.dialog_disabled'),
    ];
    $form['ui']['html_editor_disabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable HTML editor'),
      '#description' => $this->t('If checked, all HTML editor will be disabled.'),
      '#return_value' => TRUE,
      '#default_value' => $config->get('ui.html_editor_disabled'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $form_state->getValue('page')
      + $form_state->getValue('form')
      + $form_state->getValue('wizard')
      + $form_state->getValue('preview')
      + $form_state->getValue('draft')
      + $form_state->getValue('confirmation')
      + $form_state->getValue('limit');

    // Trigger update all form paths if the 'default_page_base_path' changed.
    $update_paths = ($settings['default_page_base_path'] != $this->config('yamlform.settings')->get('settings.default_page_base_path')) ? TRUE : FALSE;

    $config = $this->config('yamlform.settings');
    $config->set('settings', $settings);
    $config->set('elements', ($form_state->getValue('elements') ?: []) + ($config->get('elements') ?: []));
    $config->set('format', $form_state->getValue('format'));
    $config->set('mail', $form_state->getValue('mail'));
    $config->set('export', $this->submissionExporter->getFormValues($form_state));
    $config->set('batch', $form_state->getValue('batch'));
    $config->set('test', $form_state->getValue('test'));
    $config->set('ui', $form_state->getValue('ui'));
    $config->save();

    if ($update_paths) {
      /** @var \Drupal\yamlform\YamlFormInterface[] $yamlforms */
      $yamlforms = YamlForm::loadMultiple();
      foreach ($yamlforms as $yamlform) {
        $yamlform->updatePaths();
      }
    }
    parent::submitForm($form, $form_state);
  }

  /**
   * Wrapper for FileItem::validateExtensions.
   */
  public static function validateExtensions($element, FormStateInterface $form_state) {
    FileItem::validateExtensions($element, $form_state);
  }

  /**
   * Wrapper for FileItem::validateMaxFilesize.
   */
  public static function validateMaxFilesize($element, FormStateInterface $form_state) {
    FileItem::validateMaxFilesize($element, $form_state);
  }

}
