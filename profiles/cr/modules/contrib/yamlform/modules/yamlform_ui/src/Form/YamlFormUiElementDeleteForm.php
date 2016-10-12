<?php

namespace Drupal\yamlform_ui\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormEntityElementsValidator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for deleting a form element.
 */
class YamlFormUiElementDeleteForm extends ConfirmFormBase {

  /**
   * Form element validator.
   *
   * @var \Drupal\yamlform\YamlFormEntityElementsValidator
   */
  protected $elementsValidator;

  /**
   * The form containing the form handler to be deleted.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * A form element.
   *
   * @var \Drupal\yamlform\YamlFormElementInterface
   */
  protected $yamlformElement;

  /**
   * The form element key.
   *
   * @var string
   */
  protected $key;

  /**
   * The form element.
   *
   * @var array
   */
  protected $element;

  /**
   * Constructs a new YamlFormUiElementDeleteForm.
   *
   * @param \Drupal\yamlform\YamlFormEntityElementsValidator $elements_validator
   *   Form element validator.
   */
  public function __construct(YamlFormEntityElementsValidator $elements_validator) {
    $this->elementsValidator = $elements_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('yamlform.elements_validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $t_args = [
      '%element' => $this->getElementTitle(),
      '%yamlform' => $this->yamlform->label(),
    ];

    $build = [];
    if ($this->yamlformElement->isContainer($this->element)) {
      $build['warning'] = [
        '#markup' => $this->t('This will immediately delete the %element container and all nested elements within %element from the %yamlform form. This cannot be undone.', $t_args),
      ];
    }
    else {
      $build['warning'] = [
        '#markup' => $this->t('This will immediately delete the %element element from the %yamlform form. This cannot be undone.', $t_args),
      ];
    }

    if ($this->element['#yamlform_children']) {
      $build['elements'] = $this->getDeletedElementsItemList($this->element['#yamlform_children']);
      $build['elements']['#title'] = t('The below nested elements will be also deleted.');
    }

    return drupal_render($build);
  }

  /**
   * Get deleted elements as item list.
   *
   * @param array $children
   *   An array child key.
   *
   * @return array
   *   A render array representing an item list of elements.
   */
  protected function getDeletedElementsItemList(array $children) {
    if (empty($children)) {
      return [];
    }

    $items = [];
    foreach ($children as $key) {
      $element = $this->yamlform->getElement($key);
      if (isset($element['#title'])) {
        $title = new FormattableMarkup('@title (@key)', ['@title' => $element['#title'], '@key' => $key]);
      }
      else {
        $title = $key;
      }
      $items[$key]['title'] = ['#markup' => $title];
      if ($element['#yamlform_children']) {
        $items[$key]['items'] = $this->getDeletedElementsItemList($element['#yamlform_children']);
      }
    }

    return [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the %title element from the %yamlform form?', ['%yamlform' => $this->yamlform->label(), '%title' => $this->getElementTitle()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->yamlform->urlInfo('edit-form');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_ui_element_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $key = NULL) {
    $this->yamlform = $yamlform;
    $this->key = $key;
    $this->element = $yamlform->getElement($key);

    if ($this->element === NULL) {
      throw new NotFoundHttpException();
    }

    /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
    $element_manager = \Drupal::service('plugin.manager.yamlform.element');
    $plugin_id = $element_manager->getElementPluginId($this->element);
    $this->yamlformElement = $element_manager->createInstance($plugin_id, $this->element);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->yamlform->deleteElement($this->key);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->yamlform->save();

    drupal_set_message($this->t('The form element %title has been deleted.', ['%title' => $this->getElementTitle()]));
    $form_state->setRedirectUrl($this->yamlform->urlInfo('edit-form'));
  }

  /**
   * Get the form element's title or key.
   *
   * @return string
   *   The form element's title or key,
   */
  protected function getElementTitle() {
    return (!empty($this->element['#title'])) ? $this->element['#title'] : $this->key;
  }

}
