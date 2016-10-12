<?php

/**
 * @file
 * Hooks related to YAML Form module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the information provided in \Drupal\yamlform\Annotation\YamlFormElement.
 *
 * @param array $elements
 *   The array of form handlers, keyed on the machine-readable element name.
 */
function hook_yamlform_element_info_alter(array &$elements) {

}

/**
 * Alter the information provided in \Drupal\yamlform\Annotation\YamlFormHandler.
 *
 * @param array $handlers
 *   The array of form handlers, keyed on the machine-readable handler name.
 */
function hook_yamlform_handler_info_alter(array &$handlers) {

}

/**
 * Alter form options by id.
 *
 * @param array $options
 *   An associative array of options.
 * @param array $element
 *   The form element that the options is for.
 * @param string $options_id
 *   The form options id. Set to NULL if the options are custom.
 */
function hook_yamlform_options_alter(array &$options, array &$element, $options_id = NULL) {

}

/**
 * Alter the form options by id.
 *
 * @param array $options
 *   An associative array of options.
 * @param array $element
 *   The form element that the options is for.
 */
function hook_yamlform_options_YAMLFORM_OPTIONS_ID_alter(array &$options, array &$element) {

}

/**
 * Perform alterations before a form submission form is rendered.
 *
 * This hook is identical to hook_form_alter() but allows the
 * hook_yamlform_submission_form_alter() function to be stored in a dedicated
 * include file and it also allows the YAML Form module to implement form alter
 * logic on another module's behalf.
 *
 * @param array $form
 *   Nested array of form elements that comprise the form.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The current state of the form. The arguments that
 *   \Drupal::formBuilder()->getForm() was originally called with are available
 *   in the array $form_state->getBuildInfo()['args'].
 * @param string $form_id
 *   String representing the name of the form itself. Typically this is the
 *   name of the function that generated the form.
 *
 * @see yamlform.honeypot.inc
 * @see hook_form_BASE_FORM_ID_alter()
 * @see hook_form_FORM_ID_alter()
 *
 * @ingroup form_api
 */
function hook_yamlform_submission_form_alter(array &$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {

}

/**
 * @} End of "addtogroup hooks".
 */
