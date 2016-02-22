<?php

/**
 * @file
 * Contains \Drupal\inline_entity_form\Element\InlineEntityForm.
 */

namespace Drupal\inline_entity_form\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides an inline entity form element.
 *
 * @RenderElement("inline_entity_form")
 */
class InlineEntityForm extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#language' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      '#ief_id' => '',
      // Instance of \Drupal\Core\Entity\EntityInterface. Entity that will be
      // displayed in entity form. Can be unset if #enatity_type and #bundle
      // are provided and #op equals 'add'.
      '#entity' => NULL,
      '#entity_type' => NULL,
      '#bundle' => NULL,
      '#op' => 'add',
      // Will hide entity form's own actions if set to FALSE.
      '#display_actions' => FALSE,
      // Will save entity on submit if set to TRUE.
      '#save_entity' => TRUE,
      '#ief_element_submit' => [],
      // Needs to be set to FALSE if one wants to implement it's own submit logic.
      '#handle_submit' => TRUE,
      '#process' => [
        [$class, 'processEntityForm'],
      ],
      '#theme_wrappers' => ['container'],
    ];
  }

  /**
   * Uses inline entity form handler to add inline form to the structure.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   *
   * @see self::preRenderAjaxForm()
   */
  public static function processEntityForm($element, FormStateInterface $form_state, &$complete_form) {
    if (empty($element['#ief_id'])) {
      $element['#ief_id'] = \Drupal::service('uuid')->generate();
    }

    if (empty($element['#entity_type']) && !empty($element['#entity']) && $element['#entity'] instanceof EntityInterface) {
      $element['#entity_type'] = $element['#entity']->entityTypeId();
    }

    if (empty($element['#bundle']) && !empty($element['#entity']) && $element['#entity'] instanceof EntityInterface) {
      $element['#bundle'] = $element['#entity']->bundle();
    }

    // We can't do anything useful if we don't know which entity type/ bundle
    // we're supposed to operate with.
    if (empty($element['#entity_type']) || empty($element['#bundle'])) {
      return;
    }

    /** @var \Drupal\inline_entity_form\InlineFormInterface $ief_handler */
    $ief_handler = \Drupal::entityManager()->getHandler($element['#entity_type'], 'inline_form');

    // IEF handler is a must. If one was not assigned to this entity type we can
    // not proceed.
    if (empty($ief_handler)) {
      return;
    }

    // If entity object is not there we're displaying the add form. We need to
    // create a new entity to be used with it.
    if (empty($element['#entity'])) {
      if ($element['#op'] == 'add') {
        $values = [
          'langcode' => $element['#language'],
        ];

        $bundle_key = \Drupal::entityManager()
          ->getDefinition($element['#entity_type'])
          ->getKey('bundle');

        if ($bundle_key) {
          $values[$bundle_key] = $element['#bundle'];
        }

        $element['#entity'] = \Drupal::entityManager()
          ->getStorage($element['#entity_type'])
          ->create($values);
      }
    }

    // Put some basic information about IEF into form state.
    $state = $form_state->has(['inline_entity_form', $element['#ief_id']]) ? $form_state->get(['inline_entity_form', $element['#ief_id']]) : [];
    $state += [
      'op' => $element['#op'],
      'entity' => $element['#entity'],
    ];
    $form_state->set(['inline_entity_form', $element['#ief_id']], $state);

    $element = $ief_handler->entityForm($element, $form_state);

    // Attach submit callbacks to main submit buttons.
    if ($element['#handle_submit']) {
      static::attachMainSubmit($complete_form);
    }

    return $element;
  }

  /**
   * Tries to attach submit IEF callbacks to main submit buttons.
   *
   * @param array $complete_form
   *   Form structure.
   */
  public static function attachMainSubmit(&$complete_form) {
    $submit_attached = FALSE;
    $submit = array_merge([[get_called_class(), 'triggerIefSubmit']], $complete_form['#submit']);

    if (!empty($complete_form['submit'])) {
      if (empty($complete_form['submit']['#submit'])) {
        $complete_form['submit']['#submit'] = $submit;
      }
      else {
        $complete_form['submit']['#submit'] = array_merge([[get_called_class(), 'triggerIefSubmit']], $complete_form['submit']['#submit']);
      }
      $complete_form['submit']['#ief_submit_all'] = TRUE;
      $submit_attached = TRUE;
    }

    foreach (['submit', 'publish', 'unpublish'] as $action) {
      if (!empty($complete_form['actions'][$action])) {
        if (empty($complete_form['actions'][$action]['#submit'])) {
          $complete_form['actions'][$action]['#submit'] = $submit;
        }
        else {
          $complete_form['actions'][$action]['#submit'] = array_merge([[get_called_class(), 'triggerIefSubmit']], $complete_form['actions'][$action]['#submit']);
        }
        $complete_form['actions'][$action]['#ief_submit_all'] = TRUE;
        $submit_attached = TRUE;
      }
    }

    // If we didn't attach submit to one of the most common buttons let's search
    // the form for any submit with #button_type == primary and attach to that.
    if (!$submit_attached) {
      static::recurseAttachMainSubmit($complete_form, $submit);
    }
  }

  /**
   * Attaches IEF submit callback to primary submit element on a form.
   *
   * Recursively searches form structure and looks for submit elements with
   * #button_type == primary. If one is found it attaches IEF submit callback
   * to it and backtracks.
   *
   * @param array $element
   *   (Sub) form array structure.
   * @param array $submit_callbacks
   *   Submit callbacks to be attached.
   *
   * @return bool
   *   TRUE if appropriate element was found. FALSE otherwise.
   */
  public static function recurseAttachMainSubmit(&$element, $submit_callbacks) {
    foreach (Element::children($element) as $child) {
      if (!empty($element[$child]['#type']) && $element[$child]['#type'] == 'submit' && $element[$child]['#button_type'] == 'preview') {
        $element[$child]['#submit'] = empty($element[$child]['#submit']) ? $submit_callbacks : array_merge($submit_callbacks, $element[$child]['#submit']);
        $element[$child]['#ief_submit_all'] = TRUE;
        return TRUE;
      }
      elseif (static::recurseAttachMainSubmit($element[$child], $submit_callbacks)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Button #submit callback: Triggers submission of entity forms.
   *
   * @param $form
   *   The complete parent form.
   * @param $form_state
   *   The form state of the parent form.
   */
  public static function triggerIefSubmit($form, FormStateInterface $form_state) {
    $triggered_element = $form_state->getTriggeringElement();
    if (!empty($triggered_element['#ief_submit_all'])) {
      // The parent form was submitted, process all IEFs and their children.
      static::iefSubmit($form, $form_state);
    }
    else {
      // A specific entity form was submitted, process it and all of its children.
      $array_parents = $triggered_element['#array_parents'];
      $array_parents = array_slice($array_parents, 0, -2);
      $element = NestedArray::getValue($form, $array_parents);
      static::iefSubmit($element, $form_state);
    }
  }

  /**
   * Submits entity forms by calling their #ief_element_submit callbacks.
   *
   * #ief_element_submit is the submit version of #element_validate.
   *
   * @param array $elements
   *   An array of form elements containing entity forms.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the parent form.
   */
  public static function iefSubmit($elements, FormStateInterface $form_state) {
    // Recurse through all children.
    foreach (Element::children($elements) as $key) {
      if (!empty($elements[$key])) {
        static::iefSubmit($elements[$key], $form_state);
      }
    }

    // If there are callbacks on this level, run them.
    if (!empty($elements['#ief_element_submit'])) {
      foreach ($elements['#ief_element_submit'] as $function) {
        $function($elements, $form_state);
      }
    }
  }

}
