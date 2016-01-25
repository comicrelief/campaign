<?php
/**
 * @file
 * Contains: Drupal\pathauto\Plugin\Field\FieldWidget\PathautoWidget
 */

namespace Drupal\pathauto\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\path\Plugin\Field\FieldWidget\PathWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'pathauto' widget.
 *
 * @FieldWidget(
 *   id = "pathauto",
 *   label = @Translation("Pathauto"),
 *   field_types = {
 *     "path"
 *   }
 * )
 */
class PathautoWidget extends PathWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $entity = $items->getEntity();

    // Taxonomy terms do not have an actual fieldset for path settings.
    // Merge in the defaults.
    // @todo Impossible to do this in widget, use another solution
    /*
    $form['path'] += array(
      '#type' => 'fieldset',
      '#title' => $this->t('URL path settings'),
      '#collapsible' => TRUE,
      '#collapsed' => empty($form['path']['alias']),
      '#group' => 'additional_settings',
      '#attributes' => array(
        'class' => array('path-form'),
      ),
      '#access' => \Drupal::currentUser()->hasPermission('create url aliases') || \Drupal::currentUser()->hasPermission('administer url aliases'),
      '#weight' => 30,
      '#tree' => TRUE,
      '#element_validate' => array('path_form_element_validate'),
    );*/



    $pattern = \Drupal::service('pathauto.manager')->getPatternByEntity($entity->getEntityTypeId(), $entity->bundle(), $entity->language()->getId());
    if (empty($pattern)) {
      return $element;
    }


    if (!isset($entity->path->pathauto)) {
      if (!$entity->isNew()) {
        module_load_include('inc', 'pathauto');
        $path = \Drupal::service('path.alias_manager')->getAliasByPath('/' . $entity->urlInfo()->getInternalPath(), $entity->language()->getId());
        $pathauto_alias = \Drupal::service('pathauto.manager')->createAlias($entity->getEntityTypeId(), 'return', '/' . $entity->urlInfo()->getInternalPath(), array($entity->getEntityType()->id() => $entity), $entity->bundle(), $entity->language()->getId());
        $entity->path->pathauto = ($path != '/' . $entity->urlInfo()->getInternalPath() && $path == $pathauto_alias);
      }
      else {
        $entity->path->pathauto = TRUE;
      }
    }
    $element['pathauto'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Generate automatic URL alias'),
      '#default_value' => $entity->path->pathauto,
      '#description' => $this->t('Uncheck this to create a custom alias below. <a href="@admin_link">Configure URL alias patterns.</a>', array('@admin_link' => \Drupal::url('pathauto.patterns.form'))),
      '#weight' => -1,
    );

    // Add JavaScript that will disable the path textfield when the automatic
    // alias checkbox is checked.
    $element['alias']['#states']['!enabled']['input[name="path[pathauto]"]'] = array('checked' => TRUE);


    // Override path.module's vertical tabs summary.
    $element['alias']['#attached']['library'] = ['pathauto/widget'];

    if ($entity->path->pathauto && !empty($entity->old_alias) && empty($entity->path->alias)) {
      $element['alias']['#default_value'] = $entity->old_alias;
      $entity->path->alias = $entity->old_alias;
    }


    // For Pathauto to remember the old alias and prevent the Path module from
    // deleting it when Pathauto wants to preserve it.
    if (!empty($entity->path->alias)) {
      $element['old_alias'] = array(
        '#type' => 'value',
        '#value' => $entity->path->alias,
      );
    }

    return $element;
  }
}
