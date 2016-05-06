<?php

/**
 * @file
 * Contains \Drupal\yamlform\Form\YamlFormHandlerFormBase.
 */

namespace Drupal\yamlform\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\yamlform\YamlFormHandlerInterface;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a base form for YAML form handlers.
 */
abstract class YamlFormHandlerFormBase extends FormBase {

  /**
   * The YAML form.
   *
   * @var \Drupal\yamlform\Entity\YamlForm
   */
  protected $yamlform;

  /**
   * The YAML form handler.
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
   *   The YAML form.
   * @param string $yamlform_handler
   *   The YAML form handler ID.
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
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#default_value' => $this->yamlformHandler->label(),
      '#required' => TRUE,
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
    $form['settings']['#tree'] = TRUE;

    // Check the URL for a weight, then the YAML form handler,
    // otherwise use default.
    $form['weight'] = [
      '#type' => 'hidden',
      '#value' => $request->query->has('weight') ? (int) $request->query->get('weight') : $this->yamlformHandler->getWeight(),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => $this->yamlform->urlInfo('handlers-form'),
      '#attributes' => ['class' => ['button']],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // The YAML form handler configuration is stored in the 'settings' key in
    // the form,
    // pass that through for validation.
    $handler_data = (new FormState())->setValues($form_state->getValue('settings'));
    $this->yamlformHandler->validateConfigurationForm($form, $handler_data);
    // Update the original form values.
    $form_state->setValue('settings', $handler_data->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();

    // The YAML form handler configuration is stored in the 'settings' key in
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

    drupal_set_message($this->t('The YAML form handler was successfully applied.'));
    $form_state->setRedirectUrl($this->yamlform->urlInfo('handlers-form'));
  }

  /**
   * Generates a unique machine name for a YAML form handler instance.
   *
   * @param \Drupal\yamlform\YamlFormHandlerInterface $handler
   *   The YAML form handler.
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
   * Determines if the YAML form handler already exists.
   *
   * @param string $handler_id
   *   The YAML form handler ID.
   *
   * @return bool
   *   TRUE if the vocabulary exists, FALSE otherwise.
   */
  public function exists($handler_id) {
    $instance_ids = $this->yamlform->getHandlers()->getInstanceIds();
    return (isset($instance_ids[$handler_id])) ? TRUE : FALSE;
  }

}
