<?php

namespace Drupal\yamlform_ui\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Entity\YamlFormSubmission;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a test form for form elements.
 *
 * This form is only visible if the yamlform_devel.module is enabled.
 *
 * @see \Drupal\yamlform\Controller\YamlFormPluginElementController::index
 */
class YamlFormUiElementTestForm extends YamlFormUiElementFormBase {

  /**
   * Type of form element being tested.
   *
   * @var string
   */
  protected $type;

  /**
   * A form element.
   *
   * @var \Drupal\yamlform\YamlFormElementInterface
   */
  protected $yamlformElement;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_ui_element_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $type = NULL) {
    // Create a temp form.
    $this->yamlform = YamlForm::create(['id' => 'yamlform_ui_element_test_form']);

    $this->type = $type;

    if (!$this->elementManager->hasDefinition($type)) {
      throw new NotFoundHttpException();
    }

    $test_element = \Drupal::request()->getSession()->get('yamlform_ui_test_element_' . $type);
    if ($test_element) {
      $this->element = $test_element;
      $this->element['#type'] = $type;
    }
    else {
      $this->element = ['#type' => $type];
    }

    $this->yamlformElement = $this->elementManager->getElementInstance($this->element);

    $form['#title'] = $this->t('Test %type element', ['%type' => $type]);

    if ($test_element) {
      $yamlform_submission = YamlFormSubmission::create(['yamlform' => $this->yamlform]);
      $this->yamlformElement->initialize($test_element);
      $this->yamlformElement->initialize($this->element);
      $this->yamlformElement->prepare($this->element, $yamlform_submission);

      $form['test'] = [
        '#type' => 'details',
        '#title' => $this->t('Element test'),
        '#open' => TRUE,
        '#attributes' => [
          'style' => 'background-color: #f5f5f2',
        ],
        'element' => $this->element,
        'hr' => ['#markup' => '<hr/>'],
      ];

      if (isset($test_element['#default_value'])) {
        $html = $this->yamlformElement->formatHtml($test_element, $test_element['#default_value']);
        $form['test']['html'] = [
          '#type' => 'item',
          '#title' => $this->t('HTML'),
          '#markup' => (is_array($html)) ? drupal_render($html) : $html,
          '#allowed_tag' => Xss::getAdminTagList(),
        ];
        $form['test']['text'] = [
          '#type' => 'item',
          '#title' => $this->t('Plain text'),
          '#markup' => '<pre>' . $this->yamlformElement->formatText($test_element, $test_element['#default_value']) . '</pre>',
          '#allowed_tag' => Xss::getAdminTagList(),
        ];
      }

      $form['test']['code'] = [
        '#type' => 'item',
        '#title' => $this->t('Source'),
        'source' => [
          '#theme' => 'yamlform_codemirror',
          '#type' => 'yaml',
          '#code' => Yaml::encode($test_element),
        ],
      ];
    }

    $form['key'] = [
      '#type' => 'value',
      '#value' => 'element',
    ];
    $form['parent_key'] = [
      '#type' => 'value',
      '#value' => '',
    ];

    $form['properties'] = $this->yamlformElement->buildConfigurationForm([], $form_state);
    $form['properties']['#tree'] = TRUE;
    $form['properties']['custom']['#open'] = TRUE;

    $form['properties']['element']['type'] = [
      '#type' => 'item',
      '#title' => $this->t('Type'),
      '#markup' => $type,
      '#weight' => -100,
      '#parents' => ['type'],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Test'),
      '#button_type' => 'primary',
    ];
    if ($test_element) {
      $form['actions']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#limit_validation_errors' => [],
        '#submit' => ['::reset'],
      ];
    }
    // Clear all messages including 'Unable to display this form...' which is
    // generated because we are using a temp form.
    drupal_get_messages();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function reset(array &$form, FormStateInterface $form_state) {
    \Drupal::request()->getSession()->remove('yamlform_ui_test_element_' . $this->type);
    drupal_set_message($this->t('Form element %type test has been reset.', ['%type' => $this->type]));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Rebuild is throwing the below error.
    // LogicException: Settings can not be serialized.
    // $form_state->setRebuild();
    // @todo Determine what object is being serialized with form.
    $element_form_state = (new FormState())->setValues($form_state->getValue('properties') ?: []);
    $properties = $this->yamlformElement->getConfigurationFormProperties($form, $element_form_state);

    // Set #default_value using 'test' element value.
    if ($element_value = $form_state->getValue('element')) {
      $properties['#default_value'] = $element_value;
    }

    \Drupal::request()->getSession()->set('yamlform_ui_test_element_' . $this->type, $properties);

    drupal_set_message($this->t('Form element %type test has been updated.', ['%type' => $this->type]));
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
    return FALSE;
  }

}
