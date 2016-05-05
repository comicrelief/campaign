<?php

/**
 * @file
 * Contains \Drupal\yamlform\Form\YamlFormAdminSettingsForm.
 */

namespace Drupal\yamlform\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Plugin\Field\FieldType\FileItem;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\YamlFormElementManager;
use Drupal\yamlform\YamlFormSubmissionExporter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure YAML form admin settings for this site.
 */
class YamlFormAdminSettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The YAML form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManager
   */
  protected $elementManager;

  /**
   * The YAML form submission exporter.
   *
   * @var \Drupal\yamlform\YamlFormSubmissionExporter
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
   * @param \Drupal\yamlform\YamlFormElementManager $element_manager
   *   The YAML form element manager.
   * @param \Drupal\yamlform\YamlFormSubmissionExporter $submission_exporter
   *   The YAML form submission exporter.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $third_party_settings_manager, YamlFormElementManager $element_manager, YamlFormSubmissionExporter $submission_exporter) {
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
      '#open' => TRUE,
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
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['form']['default_form_closed_message']  = [
      '#type' => 'yamlform_codemirror_html',
      '#title' => $this->t('Default closed message'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_form_closed_message'),
    ];
    $form['form']['default_form_exception_message']  = [
      '#type' => 'yamlform_codemirror_html',
      '#title' => $this->t('Default closed exception'),
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

    $form['preview'] = [
      '#type' => 'details',
      '#title' => $this->t('Preview default settings'),
      '#open' => TRUE,
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
      '#title' => $this->t('Default previous page button label'),
      '#required' => TRUE,
      '#size' => 20,
      '#default_value' => $settings['default_preview_prev_button_label'],
    ];
    $form['preview']['default_preview_message'] = [
      '#type' => 'yamlform_codemirror_html',
      '#title' => $this->t('Default preview message'),
      '#required' => TRUE,
      '#default_value' => $settings['default_preview_message'],
    ];

    $form['draft'] = [
      '#type' => 'details',
      '#title' => $this->t('Draft default settings'),
      '#open' => TRUE,
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
      '#type' => 'yamlform_codemirror_html',
      '#title' => $this->t('Default draft save message'),
      '#required' => TRUE,
      '#default_value' => $settings['default_draft_saved_message'],
    ];
    $form['draft']['default_draft_loaded_message'] = [
      '#type' => 'yamlform_codemirror_html',
      '#title' => $this->t('Default draft load message'),
      '#required' => TRUE,
      '#default_value' => $settings['default_draft_loaded_message'],
    ];

    $form['confirmation'] = [
      '#type' => 'details',
      '#title' => $this->t('Confirmation default settings'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['confirmation']['default_confirmation_message']  = [
      '#type' => 'yamlform_codemirror_html',
      '#title' => $this->t('Default confirmation message'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_confirmation_message'),
    ];

    $form['limit'] = [
      '#type' => 'details',
      '#title' => $this->t('Limit default settings'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['limit']['default_limit_total_message']  = [
      '#type' => 'yamlform_codemirror_html',
      '#title' => $this->t('Default limit total message'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_limit_total_message'),
    ];
    $form['limit']['default_limit_user_message']  = [
      '#type' => 'yamlform_codemirror_html',
      '#title' => $this->t('Default limit user message'),
      '#required' => TRUE,
      '#default_value' => $config->get('settings.default_limit_user_message'),
    ];
    $form['inputs'] = [
      '#type' => 'details',
      '#title' => $this->t('Inputs default settings'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['inputs']['default_inputs']  = [
      '#type' => 'yamlform_codemirror_yaml',
      '#title' => $this->t('Default inputs'),
      '#description' => $this->t('These inputs will be displayed when a <a href=":href">new form</a> is created.', [':href' => Url::fromRoute('entity.yamlform.add_form')->toString()]),
      '#required' => TRUE,
      '#default_value' => $config->get('inputs.default_inputs'),
    ];
    if ($this->moduleHandler->moduleExists('file')) {
      $form['inputs']['default_max_filesize'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Default maximum upload size'),
        '#default_value' => $config->get('inputs.default_max_filesize'),
        '#description' => $this->t('Enter a value like "512" (bytes), "80 KB" (kilobytes) or "50 MB" (megabytes) in order to restrict the allowed file size. If left empty the file sizes will be limited only by PHP\'s maximum post and file upload sizes (current limit <strong>%limit</strong>).', ['%limit' => format_size(file_upload_max_size())]),
        '#element_validate' => [[get_class($this), 'validateMaxFilesize']],
        '#size' => 10,
      ];
      $form['inputs']['default_file_extensions'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Default allowed file extensions'),
        '#default_value' => $config->get('inputs.default_file_extensions'),
        '#description' => $this->t('Separate extensions with a space and do not include the leading dot.'),
        '#maxlength' => 256,
        '#element_validate' => [[get_class($this), 'validateExtensions']],
        '#required' => TRUE,
      ];
    }

    // Format.
    $form['format'] = [
      '#type' => 'details',
      '#title' => $this->t('Format default settings'),
      '#open' => TRUE,
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
        '#title' => new FormattableMarkup('@label (@id)', ['@label' => $element_plugin_label, '@id' => $element_id]),
        '#description' => $this->t('Defaults to: %value', ['%value' => $default_format_label]),
        '#options' => $formats,
        '#default_value' => $config->get("format.$element_id"),
      ];
    }
    // Mail.
    $form['mail'] = [
      '#type' => 'details',
      '#title' => $this->t('Email default settings'),
      '#open' => TRUE,
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
      '#type' => 'textarea',
      '#title' => $this->t('Default email body (Plain text)'),
      "#rows" => 10,
      '#required' => TRUE,
      '#default_value' => $config->get('mail.default_body_text'),
    ];
    $form['mail']['default_body_html']  = [
      '#type' => 'textarea',
      '#title' => $this->t('Default email body (HTML)'),
      "#rows" => 10,
      '#required' => TRUE,
      '#default_value' => $config->get('mail.default_body_html'),
    ];
    $form['mail']['token_tree_link'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [
        'yamlform',
        'yamlform-submission',
      ],
      '#dialog' => TRUE,
    ];

    // Export.
    $export_form_state = new FormState();
    $export_form_state->setValues($config->get('export') ?: []);
    $form['export'] = [
      '#type' => 'details',
      '#title' => $this->t('Export default settings'),
      '#open' => TRUE,
    ];
    $this->submissionExporter->buildForm($form, $export_form_state);

    // Batch.
    $form['batch'] = [
      '#type' => 'details',
      '#title' => $this->t('Batch settings'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['batch']['default_batch_export_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch export size'),
      '#required' => TRUE,
      '#default_value' => $config->get('batch.default_batch_export_size'),
    ];
    $form['batch']['default_batch_update_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch update size'),
      '#required' => TRUE,
      '#default_value' => $config->get('batch.default_batch_update_size'),
    ];
    $form['batch']['default_batch_delete_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Batch delete size'),
      '#required' => TRUE,
      '#default_value' => $config->get('batch.default_batch_delete_size'),
    ];

    // Test.
    $form['test'] = [
      '#type' => 'details',
      '#title' => $this->t('Test settings'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['test']['types'] = [
      '#type' => 'yamlform_codemirror_yaml',
      '#title' => $this->t('Test data by input type'),
      '#description' => $this->t("Above test data is keyed by FAPI element #type."),
      '#default_value' => $config->get('test.types'),
    ];
    $form['test']['names'] = [
      '#type' => 'yamlform_codemirror_yaml',
      '#title' => $this->t('Test data by input name'),
      '#description' => $this->t("Above test data is keyed by full or partial input names. For example, Using 'zip' will populate fields that are named 'zip' and 'zip_code' but not 'zipcode' or 'zipline'."),
      '#default_value' => $config->get('test.names'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $form_state->getValue('page')
      + $form_state->getValue('form')
      + $form_state->getValue('preview')
      + $form_state->getValue('draft')
      + $form_state->getValue('confirmation')
      + $form_state->getValue('limit');

    // Trigger update all YAML form paths if the 'default_page_base_path' changed.
    $update_paths = ($settings['default_page_base_path'] != $this->config('yamlform.settings')->get('settings.default_page_base_path')) ? TRUE : FALSE;

    $config = $this->config('yamlform.settings');
    $config->set('settings', $settings);
    $config->set('mail', $form_state->getValue('mail'));
    $config->set('batch', $form_state->getValue('batch'));
    $config->set('export', $this->submissionExporter->getFormValues($form_state));
    $config->set('test', $form_state->getValue('test'));
    $config->set('inputs', $form_state->getValue('inputs') + $config->get('inputs'));
    $config->set('format', $form_state->getValue('format'));
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
