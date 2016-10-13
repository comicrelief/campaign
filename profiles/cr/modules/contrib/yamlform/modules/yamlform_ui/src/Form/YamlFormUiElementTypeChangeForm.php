<?php

namespace Drupal\yamlform_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\yamlform\Utility\YamlFormDialogHelper;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a change element type form for a form element.
 */
class YamlFormUiElementTypeChangeForm extends YamlFormUiElementTypeFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yamlform_ui_element_type_change_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, YamlFormInterface $yamlform = NULL, $key = NULL) {
    $element = $yamlform->getElement($key);

    /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
    $yamlform_element = $this->elementManager->getElementInstance($element);

    $related_types = $yamlform_element->getRelatedTypes($element);
    if (empty($related_types)) {
      throw new NotFoundHttpException();
    }

    $headers = [
      ['data' => $this->t('Element')],
      ['data' => $this->t('Category')],
      ['data' => $this->t('Operations')],
    ];

    $definitions = $this->getDefinitions();
    $rows = [];
    foreach ($related_types as $related_type_name => $related_type_label) {
      $plugin_definition = $definitions[$related_type_name];

      $row = [];
      $row['title']['data'] = $plugin_definition['label'];
      $row['category']['data'] = (isset($plugin_definition['category'])) ? $plugin_definition['category'] : $this->t('Other');
      $row['operations']['data'] = [
        '#type' => 'operations',
        '#links' => [
          'change' => [
            'title' => $this->t('Change'),
            'url' => Url::fromRoute('entity.yamlform_ui.element.edit_form', ['yamlform' => $yamlform->id(), 'key' => $key], ['query' => ['type' => $related_type_name]]),
            'attributes' => YamlFormDialogHelper::getModalDialogAttributes(800),
          ],
        ],
      ];
      $rows[] = $row;
    }

    $form = [];
    $form['elements'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#attributes' => [
        'class' => ['yamlform-ui-element-type-table'],
      ],
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => YamlFormDialogHelper::getModalDialogAttributes(800, ['button']),
      '#url' => Url::fromRoute('entity.yamlform_ui.element.edit_form', ['yamlform' => $yamlform->id(), 'key' => $key]),
    ];

    return $form;
  }

}
