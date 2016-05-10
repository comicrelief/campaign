<?php
/**
 * @file
 * Contains Drupal\yamlform\YamlFormSubmissionForm.
 */

namespace Drupal\yamlform;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\yamlform\Controller\YamlFormController;
use Drupal\yamlform\Utility\YamlFormHelper;
use Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Base for controller for YAML form submission forms.
 */
class YamlFormSubmissionForm extends ContentEntityForm {

  /**
   * The YAML form element (plugin) manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManager
   */
  protected $elementManager;

  /**
   * The YAML form submission storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The YAML form third party settings manager.
   *
   * @var \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface
   */
  protected $thirdPartySettingsManager;

  /**
   * The YAML form settings.
   *
   * @var array
   */
  protected $settings;

  /**
   * Track if a complete custom form is being displayed instead of the ContentEntityForm.
   *
   * @var boolean
   */
  protected $isCustomForm;

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\yamlform\YamlFormElementManager $element_manager
   *   The YAML form element manager.
   * @param \Drupal\yamlform\YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager
   *   The YAML form third party settings manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, YamlFormElementManager $element_manager, YamlFormThirdPartySettingsManagerInterface $third_party_settings_manager) {
    parent::__construct($entity_manager);
    $this->elementManager = $element_manager;
    $this->storage = $this->entityManager->getStorage('yamlform_submission');
    $this->thirdPartySettingsManager = $third_party_settings_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('plugin.manager.yamlform.element'),
      $container->get('yamlform.third_party_settings_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load saved draft.
    if ($yamlform_submissions = $this->storage->loadByProperties(['in_draft' => 1, 'yamlform_id' => $this->getYamlForm()->id(), 'uid' => $this->currentUser()->id()])) {
      $yamlform_submission = reset($yamlform_submissions);
      $this->entity = $yamlform_submission;
    }

    // Store the id of YAML form entity.
    // @see _yamlform_form_after_build()
    $form['#yamlform'] = $this->getYamlForm()->id();

    // This submission form is based on the current URL, and hence it depends
    // on the 'url' cache context.
    $form['#cache']['contexts'][] = 'url';

    // Add this YAML form and the YAML form settings to the cache tags.
    $form['#cache']['tags'][] = 'config:yamlform.settings';

    // Add the YAML form as a cacheable dependency.
    $yamlform = $this->getYamlForm();
    \Drupal::service('renderer')->addCacheableDependency($form, $yamlform);

    // Display status messages.
    $this->displayMessages($form, $form_state);

    // Build the form.
    $form = parent::buildForm($form, $form_state);

    // Call custom YAML form alter hook.
    $form_id = $this->getFormId();
    $this->thirdPartySettingsManager->alter('yamlform_submission_form', $form, $form_state, $form_id);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // Check for a custom form, track it, and return it.
    if ($custom_form = $this->customForm($form, $form_state)) {
      $this->isCustomForm = TRUE;
      return $custom_form;
    }

    $form = parent::form($form, $form_state);

    /* @var $yamlform_submission \Drupal\yamlform\YamlFormSubmissionInterface */
    $yamlform_submission = $this->getEntity();
    $settings = $this->getYamlFormSettings();

    // Prepend YAML form submission data using the default view without the data.
    if (!$yamlform_submission->isNew() && !$yamlform_submission->isDraft()) {
      $form['navigation'] = [
        '#theme' => 'yamlform_submission_navigation',
        '#yamlform_submission' => $yamlform_submission,
        '#rel' => 'edit-form',
        '#weight' => -20,
      ];
      $form['information'] = [
        '#theme' => 'yamlform_submission_information',
        '#yamlform_submission' => $yamlform_submission,
        '#open' => FALSE,
        '#weight' => -19,
      ];
    }

    // Get YAML form inputs.
    $inputs = $yamlform_submission->getYamlForm()->getInputs();

    // Get submission data.
    $data = $yamlform_submission->getData();

    // Prepopulate data using query string parameters.
    if (!empty($settings['form_prepopulate'])) {
      $data += $this->getRequest()->query->all();
    }

    // Populate YAML form inputs with YAML form submission data.
    $this->populateInputs($inputs, $data);

    // Prepare YAML form inputs.
    $this->prepareInputs($inputs);

    // Handle form with managed file upload but saving of submission is disabled.
    if ($this->getYamlForm()->hasManagedFile() && !empty($settings['results_disabled'])) {
      $link = $this->getYamlForm()->link($this->t('Edit'), 'edit-form');
      $this->logger('yamlform')->notice('To support file uploads the saving of submission must be enabled. <b>All uploaded load files would be lost</b> Please either uncheck \'Disable saving of submissions\' or remove all the file upload inputs.', $link);
      $build = [
        '#markup' => $settings['form_exception_message'],
        '#allowed_tags' => Xss::getAdminTagList(),
      ];
      drupal_set_message(\Drupal::service('renderer')->render($build), 'warning');
      return $form;
    }

    // Append inputs to the form.
    $form['inputs'] = $inputs;

    // Alter form via YAML form handler.
    $this->getYamlForm()->invokeHandlers('alterForm', $form, $form_state, $yamlform_submission);

    // Add CSS and JS.
    $form['#attached']['library'][] = 'yamlform/yamlform.form';

    // Handle preview.
    if ($form_state->get('yamlform_submission_preview')) {
      // Hide inputs.
      $form['inputs']['#access'] = FALSE;

      // Display preview message.
      drupal_set_message($settings['preview_message'], 'warning');

      // Build preview.
      $form['preview'] = [
        '#theme' => 'yamlform_submission_html',
        '#yamlform_submission' => $yamlform_submission,
      ];
    }

    return $form;
  }

  /**
   * Display custom form response.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array|bool
   *   A custom form or FALSE if the default form should be built.
   */
  protected function customForm(array $form, FormStateInterface $form_state) {
    $account = $this->currentUser();
    /* @var $yamlform_submission \Drupal\yamlform\YamlFormSubmissionInterface */
    $yamlform_submission = $this->getEntity();
    $yamlform = $this->getYamlForm();
    $settings = $this->getYamlFormSettings();

    // Exit if inputs are broken, usually occurs when inputs YAML is edited
    // directly in the export config file.
    if (!$yamlform_submission->getYamlForm()->getInputs()) {
      $this->displayMessage();
      return $form;
    }

    // Display inline confirmation message which is rendered via the controller.
    if ($settings['confirmation_type'] == 'inline' && $this->getRequest()->query->get('yamlform_id') == $yamlform->id()) {
      $yamlform_controller = new YamlFormController($this->storage);
      $form['confirmation'] = $yamlform_controller->confirmation($this->getRequest(), $yamlform);
      return $form;
    }

    // Don't display form if it is closed.
    if ($yamlform_submission->isNew() && $yamlform->isClosed()) {
      // If the current user can update any submission just display the closed
      // message and still allow them to create new submissions.
      if ($yamlform->access('submission_update_any')) {
        $this->displayAdminAccessOnlyMessage();
      }
      else {
        $form['closed'] = [
          '#markup' => $settings['form_closed_message'],
          '#allowed_tags' => Xss::getAdminTagList(),
        ];
        return $form;
      }
    }

    // Disable this form if submissions are not being saved to the database or
    // passed to a YamlFormHandler.
    if (!empty($settings['results_disabled']) && !$yamlform->getHandlers(NULL, TRUE, YamlFormHandlerInterface::RESULTS_PROCESSED)->count()) {
      // Log an error.
      $link = $yamlform->toLink($this->t('Edit'), 'settings-form')->toString();
      $this->logger('yamlform')->error('%form is not saving any submitted data and has been disabled.', ['%form' => $yamlform->label(), 'link' => $link]);
      if ($this->currentUser()->hasPermission('administer yamlform')) {
        // Display error to admin but allow them to submit the broken form.
        $t_args = [
          ':settings' => $yamlform->toUrl('settings-form')->toString(),
          ':handlers' => $yamlform->toUrl('handlers-form')->toString(),
        ];
        drupal_set_message($this->t('This form is currently not saving any submitted data. Please enable the <a href=":settings">saving of results</a> or add a <a href=":handlers">submission handler</a> to the form.', $t_args), 'error');
        $this->displayAdminAccessOnlyMessage();
      }
      else {
        // Display exception message to users.
        $this->displayMessage();
        return $form;
      }
    }

    // Check limits.
    if (!empty($settings['limit_total']) && $this->storage->getTotal($yamlform) >= $settings['limit_total']) {
      $this->displayMessage('limit_total_message');
      if ($yamlform->access('submission_update_any')) {
        $this->displayAdminAccessOnlyMessage();
      }
      else {
        return $form;
      }
    }
    if (!empty($settings['limit_user']) && $account->isAuthenticated() && $this->storage->getTotal($yamlform, $account) >= $settings['limit_user']) {
      $this->displayMessage('limit_user_message');
      return $form;
    }

    return FALSE;
  }

  /**
   * Display draft and previous submission status messages for this YAML form submission.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function displayMessages(array $form, FormStateInterface $form_state) {
    /* @var $yamlform_submission \Drupal\yamlform\YamlFormSubmissionInterface */
    $yamlform_submission = $this->getEntity();
    $yamlform = $this->getYamlForm();
    $settings = $this->getYamlFormSettings();

    // Display test message.
    if ($this->getRequest()->getMethod() == 'GET' &&
      $this->getRouteMatch()->getRouteName() === 'entity.yamlform.test') {
      drupal_set_message($this->t("The below form has been prepopulated with custom/random test data. When submitted, this information <b>will still be saved</b> and/or <b>sent to designated recipients</b>."), 'warning');
    }

    // Display loaded or saved draft message.
    if ($yamlform_submission->isDraft()) {
      if ($form_state->get('yamlform_submission_draft_saved')) {
        drupal_set_message($settings['draft_saved_message']);
        $form_state->set('yamlform_submission_draft_saved', FALSE);
      }
      elseif ($this->getRequest()->getMethod() == 'GET') {
        drupal_set_message($settings['draft_loaded_message']);
      }
    }

    // Display link to previous submissions message when user is adding a new
    // submission.
    if ($this->getRequest()->getMethod() == 'GET'
      && $this->getRouteMatch()->getRouteName() == 'entity.yamlform.canonical'
      && $yamlform->access('submission_view_own')
      && $this->storage->loadByProperties(['in_draft' => 0, 'yamlform_id' => $yamlform->id(), 'uid' => $this->currentUser()->id()])) {
      drupal_set_message($this->t('You have already submitted this form. <a href=":href">View your previous submissions</a>.', [':href' => $yamlform->toUrl('submissions')->toString()]));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    // Custom forms, which completely override the ContentEntityForm, should
    // not return the actions element (aka submit buttons).
    return ($this->isCustomForm) ? NULL : parent::actionsElement($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    /* @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $this->entity;
    $settings = $this->getYamlFormSettings();
    $preview_mode = $settings['preview'];

    // No delete action on the YAML form submission form.
    unset($element['delete']);

    // Mark the submit action as the primary action, when it appears.
    $element['submit']['#button_type'] = 'primary';

    // Customize the submit button.
    $element['submit']['#value'] = $settings['form_submit_label'];

    // Add completed validate handler to submit.
    $element['submit']['#validate'][] = '::validateForm';
    $element['submit']['#validate'][] = '::complete';

    // Add confirmation submit handler to submit button.
    $element['submit']['#submit'][] = '::confirmation';

    // Preview.
    if ($preview_mode != DRUPAL_DISABLED) {
      // Only show the save button if YAML form submission previews are optional
      // or if we are already previewing the submission.
      $element['submit']['#access'] = ($yamlform_submission->id() && \Drupal::currentUser()->hasPermission('administer yamlform_submission')) || $preview_mode != DRUPAL_REQUIRED || $form_state->get('yamlform_submission_preview');

      if (!$form_state->get('yamlform_submission_preview')) {
        $element['preview'] = [
          '#type' => 'submit',
          '#value' => $settings['preview_next_button_label'],
          '#submit' => ['::submitForm', '::preview'],
          '#weight' => -1,
        ];
      }
      else {
        $element['back'] = [
          '#type' => 'submit',
          '#value' => $settings['preview_prev_button_label'],
          '#submit' => ['::submitForm', '::back'],
          '#weight' => -1,
        ];
      }
    }

    // Draft.
    if ($this->draftEnabled()) {
      $element['draft'] = [
        '#type' => 'submit',
        '#value' => $settings['draft_button_label'],
        '#validate' => ['::draft'],
        '#submit' => ['::submitForm', '::save', '::rebuild'],
        '#weight' => -10,
      ];
    }

    return $element;
  }

  /**
   * Prepare form inputs.
   *
   * @param array $inputs
   *   An render array representing inputs.
   */
  protected function prepareInputs(array &$inputs) {
    foreach ($inputs as $key => &$element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }

      // Replace tokens for all properties.
      foreach ($element as $element_property => $element_value) {
        $element[$element_property] = $this->replaceTokens($element_value);
      }

      // Remove #test property.
      unset($element['#test']);

      // Replace default_value tokens
      // Invoke YamlFormElement::prepare.
      $this->elementManager->invokeMethod('prepare', $element, $this->entity);

      // Initialize default values.
      if (isset($element['#default_value'])) {
        // Invoke YamlFormElement::setDefaultValue.
        $this->elementManager->invokeMethod('setDefaultValue', $element);
      }

      $this->prepareInputs($element);
    }
  }

  /**
   * Populate form inputs.
   *
   * @param array $inputs
   *   An render array representing inputs.
   * @param array $values
   *   An array of values used to populate the inputs.
   */
  protected function populateInputs(array &$inputs, array $values) {
    foreach ($inputs as $key => &$element) {
      // Skip if not a FAPI element.
      if (Element::property($key) || !is_array($element) || !isset($element['#type'])) {
        continue;
      }
      // Populate inputs if value exists.
      if (isset($values[$key])) {
        $element['#default_value'] = $values[$key];
      }
      $this->populateInputs($element, $values);
    }
  }

  /**
   * Form submission handler for the 'preview' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function preview(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->autosave($form, $form_state);
      $form_state->set('yamlform_submission_preview', TRUE);
      $form_state->setRebuild();
    }
  }

  /**
   * Form submission handler for the 'back' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function back(array &$form, FormStateInterface $form_state) {
    $form_state->set('yamlform_submission_preview', FALSE);
    $form_state->setRebuild();
  }

  /**
   * Form submission handler for the 'draft' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function draft(array &$form, FormStateInterface $form_state) {
    $form_state->clearErrors();
    $form_state->setValue('in_draft', TRUE);
    $form_state->set('yamlform_submission_draft_saved', TRUE);
    $this->entity->validate();
  }

  /**
   * Form submission handler for the 'complete' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function complete(array &$form, FormStateInterface $form_state) {
    $form_state->setValue('in_draft', FALSE);
  }

  /**
   * Form submission handler for the 'rebuild' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function rebuild(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * Form submission handler for the 'autosave' action.
   *
   * Autosave is triggered by validation errors and/or from preview.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function autosave(array &$form, FormStateInterface $form_state) {
    $settings = $this->getYamlFormSettings();
    if ($this->draftEnabled() && $settings['draft_auto_save'] && !$this->entity->isCompleted()) {
      $form_state->setValue('in_draft', TRUE);
      $this->submitForm($form, $form_state);
      $this->save($form, $form_state);
      $this->rebuild($form, $form_state);
    }
  }

  /**
   * Form submission handler for the 'confirmation' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function confirmation(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $this->getEntity();
    $yamlform = $yamlform_submission->getYamlForm();
    $settings = $this->getYamlFormSettings();

    $state = $yamlform_submission->getState();

    // Default to displaying a confirmation message on this page.
    if ($state == YamlFormSubmissionInterface::STATE_UPDATED) {
      drupal_set_message($this->t('Submission updated in %form.', ['%form' => $yamlform->label()]));
      $form_state->setRedirect('entity.yamlform_submission.canonical', ['yamlform_submission' => $yamlform_submission->id()]);
      return;
    }

    // Get route options with query token.
    $route_options = [];
    if ($query = $this->getRequest()->query->all()) {
      $route_options['query'] = $query;
    }
    if ($state == YamlFormSubmissionInterface::STATE_COMPLETED) {
      $route_options['query']['token'] = $yamlform_submission->getToken();
    }

    // Handle 'inline', 'page', and 'url' confirmation types.
    switch ($settings['confirmation_type']) {
      case 'inline':
        $route_options['query']['yamlform_id'] = $yamlform->id();
        $form_state->setRedirect('<current>', [], $route_options);
        return;

      case 'page':
        $form_state->setRedirect('entity.yamlform.confirmation', ['yamlform' => $yamlform->id()], $route_options);
        return;

      case 'url':
        if ($confirmation_url = \Drupal::pathValidator()->getUrlIfValid(trim($settings['confirmation_url'])) ?: NULL) {
          if ($settings['confirmation_message']) {
            drupal_set_message($settings['confirmation_message']);
          }
          $form_state->setRedirectUrl($confirmation_url);
          return;
        }
    }

    // Finally default to a confirmation message.
    if ($settings['confirmation_message']) {
      drupal_set_message($settings['confirmation_message']);
    }
    else {
      drupal_set_message($this->t('New submission added to %form.', ['%form' => $yamlform->label()]));
    }

    $route_parameters = ['yamlform' => $yamlform->id()];
    $route_options = [];
    if ($query = $this->getRequest()->query->all()) {
      $route_options['query'] = $query;
    }
    $form_state->setRedirect('entity.yamlform.canonical', $route_parameters, $route_options);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Validate form via YAML form handler.
    $this->getYamlForm()->invokeHandlers('validateForm', $form, $form_state, $this->entity);

    // If there are validation errors try to autosave this submission.
    if ($form_state->hasAnyErrors()) {
      $this->autosave($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Remove values that are not YAML form inputs.
    $values = YamlFormHelper::cleanupFormStateValues($values, ['uid', 'yamlform_id', 'submit']);

    /* @var $yamlform_submission \Drupal\yamlform\YamlFormSubmissionInterface */
    $yamlform_submission = $this->entity;

    // Serialize the values as YAML and merge existing data.
    $yamlform_submission->setData($values + $yamlform_submission->getData());

    parent::submitForm($form, $form_state);

    // Submit form via YAML form handler.
    $this->getYamlForm()->invokeHandlers('submitForm', $form, $form_state, $yamlform_submission);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $this->getEntity();
    $yamlform = $yamlform_submission->getYamlForm();
    $settings = $this->getYamlFormSettings();

    // Exit if the saving of results is disabled.
    if ($settings['results_disabled']) {
      return;
    }

    // Make sure the uri and remote addr are set correctly because
    // AJAX requests via 'managed_file' uploads can cause these values to be
    // reset.
    if ($yamlform_submission->isNew()) {
      $yamlform_submission->set('uri', preg_replace('#^' . base_path() . '#', '/', $this->getRequest()->getRequestUri()));
      $yamlform_submission->set('remote_addr', $this->getRequest()->getClientIp());
    }

    $yamlform_submission->save();

    // Log submission saved transaction.
    $link = $yamlform_submission->toLink($this->t('Edit'), 'edit-form')->toString();
    switch ($yamlform_submission->getState()) {
      case YamlFormSubmissionInterface::STATE_DRAFT;
        $this->logger('yamlform')->notice('Submission draft saved in %form.', ['%form' => $yamlform->label(), 'link' => $link]);
        break;

      case YamlFormSubmissionInterface::STATE_UPDATED;
        $this->logger('yamlform')->notice('Submission updated in %form.', ['%form' => $yamlform->label(), 'link' => $link]);
        break;

      case YamlFormSubmissionInterface::STATE_COMPLETED;
        $this->logger('yamlform')->notice('New submission added to %form.', ['%form' => $yamlform->label(), 'link' => $link]);
        break;
    }

    // Check limits and invalidate cached and rebuild.
    if ($this->checkLimits()) {
      Cache::invalidateTags(['yamlform:' . $this->getYamlForm()->id()]);
      $form_state->setRebuild();
    }
  }

  /**
   * Check YAML form submission limits.
   *
   * @return bool
   *   TRUE if YAML form submission limits have been met.
   */
  public function checkLimits() {
    $account = $this->currentUser();
    $yamlform = $this->getYamlForm();
    $settings = $this->getYamlFormSettings();

    if (!empty($settings['limit_total']) && $this->storage->getTotal($yamlform) >= $settings['limit_total']) {
      return TRUE;
    }
    elseif (!empty($settings['limit_user']) && $account->isAuthenticated() && $this->storage->getTotal($yamlform, $account) >= $settings['limit_user']) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Determine if drafts are enabled.
   *
   * @return bool
   *   TRUE if drafts are enabled.
   */
  protected function draftEnabled() {
    $settings = $this->getYamlFormSettings();
    return ($this->currentUser()->isAuthenticated() && !empty($settings['draft']) && empty($settings['results_disabled'])) ? TRUE : FALSE;
  }

  /****************************************************************************/
  // Helper functions
  /****************************************************************************/

  /**
   * Get the YAML form submission's YAML form.
   *
   * @return \Drupal\yamlform\Entity\YamlForm
   *   A YAML form.
   */
  protected function getYamlForm() {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    $yamlform_submission = $this->getEntity();
    return $yamlform_submission->getYamlForm();
  }

  /**
   * Get the YAML form submission's YAML form settings.
   *
   * @return array
   *   A specific settings or an associative array of settings.
   */
  protected function getYamlFormSettings() {
    if (empty($this->settings)) {
      // Get YAML form settings with  default values.
      $this->settings = $this->getYamlForm()->getSettings();
      $default_settings = $this->config('yamlform.settings')->get('settings');
      foreach ($default_settings as $key => $value) {
        $key = str_replace('default_', '', $key);
        if (empty($this->settings[$key])) {
          $this->settings[$key] = $value;
        }

      }
      foreach ($this->settings as $key => $value) {
        $this->settings[$key] = $this->replaceTokens($value);
      }
    }

    return $this->settings;
  }

  /**
   * Replace tokens in text.
   *
   * @param string $text
   *   A string of text that main contain tokens.
   *
   * @return string
   *   Text will tokens replaced.
   */
  protected function replaceTokens($text) {
    // Most strings won't contain tokens so lets check and return ASAP.
    if (!is_string($text) || strpos($text, '[') === FALSE) {
      return $text;
    }
    $token_data = [
      'yamlform' => $this->getYamlForm(),
      'yamlform_submission' => $this->entity,
    ];
    return \Drupal::token()->replace($text, $token_data);
  }

  /**
   * Display message.
   *
   * @param string $key
   *   The name of YAML form settings message to be displayed.
   * @param string $type
   *   (optional) The message's type. Defaults to 'warning'. These values are
   *   supported:
   *   - 'status'
   *   - 'warning'
   *   - 'error'
   */
  protected function displayMessage($key = 'form_exception_message', $type = 'warning') {
    $settings = $this->getYamlFormSettings();
    $build = [
      '#markup' => $settings[$key],
      '#allowed_tags' => Xss::getAdminTagList(),
    ];
    drupal_set_message(\Drupal::service('renderer')->renderPlain($build), $type);
  }

  /**
   * Display admin access only message.
   */
  protected function displayAdminAccessOnlyMessage() {
    $t_args = [
      ':href' => Url::fromRoute('entity.yamlform.settings_form', ['yamlform' => $this->getYamlForm()->id()])->toString(),
    ];
    drupal_set_message($this->t('This form is <a href=":href">closed</a>. Only submission administrators are allowed to access this form and create new submissions.', $t_args), 'warning');
  }

}
