<?php

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\yamlform\YamlFormDialogTrait;
use Drupal\yamlform\YamlFormHandlerInterface;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a base form for form handlers.
 */
abstract class YamlFormHandlerFormBase extends FormBase {

  use YamlFormDialogTrait;

  /**
   * The form.
   *
   * @var \Drupal\yamlform\Entity\YamlForm
   */
  protected $yamlform;

  /**
   * The form handler.
   *
   * @var \Drupal\yamlform\YamlFormHandlerInterface
   */
  protected $yamlformHandler;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_handler_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   The form.
   * @param string $yamlform_handler
   *   The form handler ID.
   *
   * @return array
   *   The form structure.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws not found exception if the number of handler instances for this
   *   form exceeds the handler's cardinality.
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $yamlform_handler = NULL) {
    $this->yamlform = $yamlform;
    try {
      $this->yamlformHandler = $this->prepareYamlFormHandler($yamlform_handler);
    }
    catch (PluginNotFoundException $e) {
      throw new NotFoundHttpException("Invalid handler id: '$yamlform_handler'.");
    }

    // Limit the number of plugin instanced allowed.
    if (!$this->yamlformHandler->getHandlerId()) {
      $plugin_id = $this->yamlformHandler->getPluginId();
      $cardinality = $this->yamlformHandler->cardinality();
      $number_of_instances = $yamlform->getHandlers($plugin_id)->count();
      if ($cardinality !== YamlFormHandlerInterface::CARDINALITY_UNLIMITED && $cardinality <= $number_of_instances) {
        $t_args = ['@number' => $cardinality, '@instances' => $this->formatPlural($cardinality, $this->t('instance is'), $this->t('instances are'))];
        throw new NotFoundHttpException($this->t('Only @number @instance permitted', $t_args));
      }
    }

    $request = $this->getRequest();

    $form['description'] = [
      '#markup' => $this->yamlformHandler->description(),
      '#prefix' => '<p>',
      '#suffix' => '</p>',
    ];

    $form['id'] = [
      '#type' => 'value',
      '#value' => $this->yamlformHandler->getPluginId(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable the %name handler.', ['%name' => $this->yamlformHandler->label()]),
      '#default_value' => $this->yamlformHandler->isEnabled(),
      // Disable broken plugins.
      '#disabled' => ($this->yamlformHandler->getPluginId() == 'broken'),
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#default_value' => $this->yamlformHandler->label(),
      '#required' => TRUE,
      '#attributes' => ['autofocus' => 'autofocus'],
    ];

    $form['handler_id'] = [
      '#type' => 'machine_name',
      '#maxlength' => 64,
      '#description' => $this->t('A unique name for this handler instance. Must be alpha-numeric and underscore separated.'),
      '#default_value' => $this->yamlformHandler->getHandlerId() ?: $this->getUniqueMachineName($this->yamlformHandler),
      '#required' => TRUE,
      '#disabled' => $this->yamlformHandler->getHandlerId() ? TRUE : FALSE,
      '#machine_name' => [
        'exists' => [$this, 'exists'],
      ],
    ];

    $form['settings'] = $this->yamlformHandler->buildConfigurationForm([], $form_state);
    // Get $form['settings']['#attributes']['novalidate'] and apply it to the
    // $form.
    // This allows handlers with hide/show logic to skip HTML5 validation.
    // @see http://stackoverflow.com/questions/22148080/an-invalid-form-control-with-name-is-not-focusable
    if (isset($form['settings']['#attributes']['novalidate'])) {
      $form['#attributes']['novalidate'] = 'novalidate';
    }
    $form['settings']['#tree'] = TRUE;

    // Check the URL for a weight, then the form handler,
    // otherwise use default.
    $form['weight'] = [
      '#type' => 'hidden',
      '#value' => $request->query->has('weight') ? (int) $request->query->get('weight') : $this->yamlformHandler->getWeight(),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    $form = $this->buildDialog($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // The form handler configuration is stored in the 'settings' key in
    // the form, pass that through for validation.
    $settings = $form_state->getValue('settings') ?: [];
    $handler_state = (new FormState())->setValues($settings);
    $this->yamlformHandler->validateConfigurationForm($form, $handler_state);

    // Process handler state form errors.
    $this->processHandlerFormErrors($handler_state, $form_state);

    // Update the original form values.
    $form_state->setValue('settings', $handler_state->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($response = $this->validateDialog($form, $form_state)) {
      return $response;
    }

    $form_state->cleanValues();

    // The form handler configuration is stored in the 'settings' key in
    // the form, pass that through for submission.
    $handler_data = (new FormState())->setValues($form_state->getValue('settings'));

    $this->yamlformHandler->submitConfigurationForm($form, $handler_data);
    // Update the original form values.
    $form_state->setValue('settings', $handler_data->getValues());

    $is_new = ($this->yamlformHandler->getHandlerId()) ? FALSE : TRUE;

    $this->yamlformHandler->setHandlerId($form_state->getValue('handler_id'));
    $this->yamlformHandler->setLabel($form_state->getValue('label'));
    $this->yamlformHandler->setStatus($form_state->getValue('status'));
    $this->yamlformHandler->setWeight($form_state->getValue('weight'));
    if ($is_new) {
      $this->yamlform->addYamlFormHandler($this->yamlformHandler->getConfiguration());
    }
    $this->yamlform->save();

    // Display status message.
    drupal_set_message($this->t('The form handler was successfully applied.'));

    // Redirect.
    return $this->redirectForm($form, $form_state, $this->yamlform->urlInfo('handlers-form'));
  }

  /**
   * Generates a unique machine name for a form handler instance.
   *
   * @param \Drupal\yamlform\YamlFormHandlerInterface $handler
   *   The form handler.
   *
   * @return string
   *   Returns the unique name.
   */
  public function getUniqueMachineName(YamlFormHandlerInterface $handler) {
    $suggestion = $handler->getPluginId();
    $count = 1;
    $machine_default = $suggestion;
    $instance_ids = $this->yamlform->getHandlers()->getInstanceIds();
    while (isset($instance_ids[$machine_default])) {
      $machine_default = $suggestion . '_' . $count++;
    }
    // Only return a suggestion if it is not the default plugin id.
    return ($machine_default != $handler->getPluginId()) ? $machine_default : '';
  }

  /**
   * Determines if the form handler ID already exists.
   *
   * @param string $handler_id
   *   The form handler ID.
   *
   * @return bool
   *   TRUE if the form handler ID exists, FALSE otherwise.
   */
  public function exists($handler_id) {
    $instance_ids = $this->yamlform->getHandlers()->getInstanceIds();

    return (isset($instance_ids[$handler_id])) ? TRUE : FALSE;
  }

  /**
   * Process handler form errors in form.
   *
   * @param FormStateInterface $handler_state
   *   The form handler form state.
   * @param FormStateInterface &$form_state
   *   The form state.
   */
  protected function processHandlerFormErrors(FormStateInterface $handler_state, FormStateInterface &$form_state) {
    foreach ($handler_state->getErrors() as $name => $message) {
      $form_state->setErrorByName($name, $message);
    }
  }

}
