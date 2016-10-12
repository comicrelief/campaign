<?php

namespace Drupal\yamlform_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\yamlform\Utility\YamlFormDialogHelper;
use Drupal\yamlform\YamlFormDialogTrait;
use Drupal\yamlform\YamlFormElementManagerInterface;
use Drupal\yamlform\YamlFormEntityElementsValidator;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for form element forms.
 */
abstract class YamlFormUiElementFormBase extends FormBase implements YamlFormUiElementFormInterface {

  use YamlFormDialogTrait;

  /**
   * Form element manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * Form element validator.
   *
   * @var \Drupal\yamlform\YamlFormEntityElementsValidator
   */
  protected $elementsValidator;

  /**
   * The form.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The form element.
   *
   * @var array
   */
  protected $element = [];

  /**
   * The form element's original element type.
   *
   * @var string
   */
  protected $originalType;

  /**
   * The action of the current form.
   *
   * @var string
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_ui_element_form';
  }

  /**
   * Constructs a new YamlFormUiElementFormBase.
   *
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $element_manager
   *   The form element manager.
   * @param \Drupal\yamlform\YamlFormEntityElementsValidator $elements_validator
   *   Form element validator.
   */
  public function __construct(YamlFormElementManagerInterface $element_manager, YamlFormEntityElementsValidator $elements_validator) {
    $this->elementManager = $element_manager;
    $this->elementsValidator = $elements_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.yamlform.element'),
      $container->get('yamlform.elements_validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $key = NULL, $parent_key = '') {
    $this->yamlform = $yamlform;

    $yamlform_element = $this->getYamlFormElement();

    $form['parent_key'] = [
      '#type' => 'value',
      '#value' => $parent_key,
    ];

    $form['key'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Key'),
      '#default_value' => $key,
      '#machine_name' => [
        'label' => $this->t('Key'),
        'exists' => [$this, 'exists'],
        'source' => ['title'],
      ],
      '#disabled' => $key,
      '#required' => TRUE,
    ];

    // Remove the key's help text (aka description) once it has been set.
    if ($key) {
      $form['key']['#description'] = NULL;
    }

    $form['properties'] = $yamlform_element->buildConfigurationForm([], $form_state);

    // Move messages to the top of the form.
    if (isset($form['properties']['messages'])) {
      $form['messages'] = $form['properties']['messages'];
      $form['messages']['#weight'] = -100;
      unset($form['properties']['messages']);
    }

    // Hide #flex property if parent element is not a 'yamlform_flexbox'.
    if (isset($form['properties']['flex']) && !$this->isParentElementFlexbox($key, $parent_key)) {
      $form['properties']['flex']['#access'] = FALSE;
    }

    // Add type to the general details.
    $form['properties']['element']['type'] = [
      '#type' => 'item',
      '#title' => $this->t('Type'),
      'label' => [
        '#markup' => $yamlform_element->getPluginLabel(),
      ],
      '#weight' => -100,
      '#parents' => ['type'],
    ];

    // Allows users to change element type.
    if ($key && $yamlform_element->getRelatedTypes($this->element)) {
      $route_parameters = ['yamlform' => $yamlform->id(), 'key' => $key];
      if ($this->originalType) {
        $original_yamlform_element = $this->elementManager->createInstance($this->originalType);
        $route_parameters = ['yamlform' => $yamlform->id(), 'key' => $key];
        $form['properties']['element']['type']['cancel'] = [
          '#type' => 'link',
          '#title' => $this->t('Cancel'),
          '#url' => new Url('entity.yamlform_ui.element.edit_form', $route_parameters),
          '#attributes' => YamlFormDialogHelper::getModalDialogAttributes(800, ['button', 'button--small']),
        ];
        $form['properties']['element']['type']['#description'] = '(' . $this->t('Changing from %type', ['%type' => $original_yamlform_element->getPluginLabel()]) . ')';
      }
      else {
        $form['properties']['element']['type']['change_type'] = [
          '#type' => 'link',
          '#title' => $this->t('Change'),
          '#url' => new Url('entity.yamlform_ui.change_element', $route_parameters),
          '#attributes' => YamlFormDialogHelper::getModalDialogAttributes(800, ['button', 'button--small']),
        ];
      }
    }

    // Use title for key (machine_name).
    if (isset($form['properties']['element']['title'])) {
      $form['key']['#machine_name']['source'] = ['properties', 'element', 'title'];
      $form['properties']['element']['title']['#id'] = 'title';
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#_validate_form' => TRUE,
    ];

    $form = $this->buildDialog($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Only validate the submit button.
    $button = $form_state->getTriggeringElement();
    if (empty($button['#_validate_form'])) {
      return;
    }

    $yamlform_element = $this->getYamlFormElement();

    // The form element configuration is stored in the 'properties' key in
    // the form, pass that through for validation.
    $element_form_state = (new FormState())->setValues($form_state->getValue('properties') ?: []);
    $element_form_state->setFormObject($this);

    // Validate configuration form and set form errors.
    $yamlform_element->validateConfigurationForm($form, $element_form_state);
    $element_errors = $element_form_state->getErrors();
    foreach ($element_errors as $element_error) {
      $form_state->setErrorByName(NULL, $element_error);
    }

    // Stop validation is the element properties has any errors.
    if ($form_state->hasAnyErrors()) {
      return;
    }

    // Set element properties.
    $properties = $yamlform_element->getConfigurationFormProperties($form, $element_form_state);
    $parent_key = $form_state->getValue('parent_key');
    $key = $form_state->getValue('key');
    if ($key) {
      $this->yamlform->setElementProperties($key, $properties, $parent_key);

      // Validate elements.
      if ($messages = $this->elementsValidator->validate($this->yamlform)) {
        $t_args = [':href' => Url::fromRoute('entity.yamlform.source_form', ['yamlform' => $this->yamlform->id()])->toString()];
        $form_state->setErrorByName('elements', $this->t('There has been error validating the elements. You may need to edit the <a href=":href">YAML source</a> to resolve the issue.', $t_args));
        foreach ($messages as $message) {
          drupal_set_message($message, 'error');
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $yamlform_element = $this->getYamlFormElement();

    if ($response = $this->validateDialog($form, $form_state)) {
      return $response;
    }

    // The form element configuration is stored in the 'properties' key in
    // the form, pass that through for submission.
    $element_data = (new FormState())->setValues($form_state->getValue('properties'));
    $yamlform_element->submitConfigurationForm($form, $element_data);
    $this->yamlform->save();

    // Display status message.
    $properties = $form_state->getValue('properties');
    $t_args = [
      '%title' => (!empty($properties['title'])) ? $properties['title'] : $form_state->getValue('key'),
      '@action' => $this->action,
    ];
    drupal_set_message($this->t('%title has been @action.', $t_args));

    // Redirect.
    return $this->redirectForm($form, $form_state, $this->yamlform->urlInfo('edit-form'));
  }

  /**
   * Determines if the form element key already exists.
   *
   * @param string $key
   *   The form element key.
   *
   * @return bool
   *   TRUE if the form element key, FALSE otherwise.
   */
  public function exists($key) {
    $elements = $this->yamlform->getElementsInitializedAndFlattened();
    return (isset($elements[$key])) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isNew() {
    return ($this instanceof YamlFormUiElementAddForm) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlForm() {
    return $this->yamlform;
  }

  /**
   * {@inheritdoc}
   */
  public function getYamlFormElement() {
    return $this->elementManager->getElementInstance($this->element);
  }

  /**
   * Determine if the parent element is a 'yamlform_flexbox'.
   *
   * @param string|null $key
   *   The element's key. Only applicable for existing elements.
   * @param string|null $parent_key
   *   The element's parent key. Only applicable for new elements.
   *   Parent key is set via query string parameter. (?parent={parent_key})
   *
   * @return bool
   *   TRUE if the parent element is a 'yamlform_flexbox'.
   */
  protected function isParentElementFlexbox($key = NULL, $parent_key = NULL) {
    $elements = $this->yamlform->getElementsInitializedAndFlattened();

    // Check the element #yamlform_parent_flexbox property.
    if ($key && isset($elements[$key])) {
      return $elements[$key]['#yamlform_parent_flexbox'];
    }

    // Check the parent element #type.
    if ($parent_key && isset($elements[$parent_key]) && isset($elements[$parent_key]['#type'])) {
      return ($elements[$parent_key]['#type'] == 'yamlform_flexbox') ? TRUE : FALSE;
    }

    return FALSE;
  }

}
