<?php
/**
 * @file
 * Contains Drupal\yamlform\YamlFormEntityInputsValidator.
 */

namespace Drupal\yamlform;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Form\FormState;
use Drupal\yamlform\Entity\YamlForm;
use Drupal\yamlform\Utility\YamlFormArrayHelper;

/**
 * Defines a class to validate YAML form inputs.
 */
class YamlFormEntityInputsValidator {

  use StringTranslationTrait;

  /**
   * The YAML form being validated.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The raw inputs value.
   *
   * @var string
   */
  protected $inputsRaw;

  /**
   * The raw original inputs value.
   *
   * @var string
   */
  protected $originalInputsRaw;

  /**
   * The parsed inputs array.
   *
   * @var array
   */
  protected $inputs;

  /**
   * The parsed original inputs array.
   *
   * @var array
   */
  protected $originalInputs;

  /**
   * Validate YAML form inputs.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A YAML form.
   *
   * @return array|null
   *   An array of error messages or NULL is the inputs are valid.
   */
  public function validate(YamlFormInterface $yamlform) {
    $this->yamlform = $yamlform;

    $this->inputsRaw = $yamlform->getInputsRaw();
    $this->originalInputsRaw = $yamlform->getOriginalInputsRaw();

    // Validate required.
    if ($message = $this->validateRequired()) {
      return [$message];
    }
    // Validate contain valid YAML.
    if ($message = $this->validateYaml()) {
      return [$message];
    }

    $this->inputs = Yaml::decode($this->inputsRaw);
    $this->originalInputs = Yaml::decode($this->originalInputsRaw);

    // Validate inputs are an array.
    if ($message  = $this->validateArray()) {
      return [$message];
    }

    // Validate translation.
    if ($message = $this->validateTranslation()) {
      return [$message];
    }

    // Validate duplicate input name.
    if ($messages = $this->validateDuplicateNames()) {
      return $messages;
    }

    // Validate ignored properties.
    if ($messages = $this->validateProperties()) {
      return $messages;
    }

    // Validate submission data.
    if ($messages = $this->validateSubmissions()) {
      return $messages;
    }

    // Validate rendering.
    if ($message = $this->validateRendering()) {
      return [$message];
    }

    return NULL;
  }

  /**
   * Validate inputs are required.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateRequired() {
    return (empty($this->inputsRaw)) ? $this->t('Inputs are required') : NULL;
  }

  /**
   * Validate inputs is validate YAML.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateYaml() {
    try {
      Yaml::decode($this->inputsRaw);
      return NULL;
    }
    catch (\Exception $exception) {
      return $this->t('Inputs are not valid. @message', ['@message' => $exception->getMessage()]);
    }
  }

  /**
   * Validate inputs are an array of elements.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateArray() {
    if (!is_array($this->inputs)) {
      return $this->t('Inputs are not valid. YAML must contain an associative array of inputs.');
    }
    return NULL;
  }

  /**
   * Validate inputs does not contain duplicate names.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateDuplicateNames() {
    $duplicate_names = [];
    $this->getDuplicateNamesRecursive($this->inputs, $duplicate_names);
    if ($duplicate_names = array_filter($duplicate_names)) {
      $messages = [];
      foreach ($duplicate_names as $duplicate_name => $duplicate_count) {
        $line_numbers = $this->getLineNumbers('/^\s*(["\']?)' . preg_quote($duplicate_name, '/') . '\1\s*:/');
        $t_args = [
          '%name' => $duplicate_name,
          '@lines' => $this->formatPlural(count($line_numbers), $this->t('line'), $this->t('lines')),
          '@line_numbers' => YamlFormArrayHelper::toString($line_numbers),
        ];
        $messages[] = $this->t('Inputs contain a duplicate element name %name found on @lines @line_numbers.', $t_args);
      }
      return $messages;
    }
    return NULL;
  }

  /**
   * Recurse through elements and collect an associative array keyed by name and number of duplicate instances.
   *
   * @param array $elements
   *   An array of elements.
   * @param array $names
   *   An associative array keyed by name and number of duplicate instances.
   */
  protected function getDuplicateNamesRecursive(array $elements, array &$names) {
    foreach ($elements as $key => &$element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }
      if (isset($element['#type'])) {
        if (!isset($names[$key])) {
          $names[$key] = 0;
        }
        else {
          ++$names[$key];
        }
      }
      $this->getDuplicateNamesRecursive($element, $names);
    }
  }

  /**
   * Validate that inputs are not using ignored properties.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateProperties() {
    $ignored_properties = [];
    $this->getIgnoredPropertiesRecursive($this->inputs, $ignored_properties);
    if ($ignored_properties = array_filter($ignored_properties)) {
      $messages = [];
      foreach ($ignored_properties as $ignored_property) {
        $line_numbers = $this->getLineNumbers('/^\s*(["\']?)' . preg_quote($ignored_property, '/') . '\1\s*:/');
        $t_args = [
          '%property' => $ignored_property,
          '@lines' => $this->formatPlural(count($line_numbers), $this->t('line'), $this->t('lines')),
          '@line_numbers' => YamlFormArrayHelper::toString($line_numbers),
        ];
        $messages[] = $this->t('Inputs contain a unsupported %property property found on @lines @line_numbers.', $t_args);
      }
      return $messages;
    }
    return NULL;
  }

  /**
   * Recurse through elements and collect an associative array of ignored properties.
   *
   * @param array $elements
   *   An array of elements.
   * @param array $properties
   *   An array tracking ignored properties.
   */
  protected function getIgnoredPropertiesRecursive(array $elements, array &$properties) {
    $ignored_properties = YamlForm::getIgnoredProperties();
    foreach ($elements as $key => &$element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }

      $properties += array_intersect_key($ignored_properties, $element);
      $this->getIgnoredPropertiesRecursive($element, $properties);
    }
  }

  /**
   * Validate that input are not deleted when the YAML form has submissions.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateSubmissions() {
    if (!$this->yamlform->hasSubmissions()) {
      return NULL;
    }

    $input_names = [];
    $this->getInputsNamesRecursive($this->inputs, $input_names);
    $original_input_names = [];
    $this->getInputsNamesRecursive($this->originalInputs, $original_input_names);
    if ($missing_input_names = array_diff_key($original_input_names, $input_names)) {
      $messages = [];
      foreach ($missing_input_names as $missing_input_name) {
        $t_args = [
          ':results_href' => $this->yamlform->toUrl('results-submissions')->toString(),
          ':clear_href' => $this->yamlform->toUrl('results-clear')->toString(),
          '%title' => $this->yamlform->label(),
          '%name' => $missing_input_name,
        ];
        $messages[] = $this->t('The %title form has <a href=":results_href">results</a>. The %name input can not be removed. You can either hide this input by setting its <code>\'#access\'</code> property to <code>false</code> or by <a href=":clear_href">deleting all the submitted results</a>.', $t_args);
      }
      return $messages;
    }

    return NULL;
  }

  /**
   * Recurse through elements and collect an associative array of deleted input names.
   *
   * @param array $elements
   *   An array of elements.
   * @param array $names
   *   An array tracking deleted input names.
   */
  protected function getInputsNamesRecursive(array $elements, array &$names) {
    foreach ($elements as $key => &$element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }
      if (isset($element['#type'])) {
        $names[$key] = $key;
      }
      $this->getInputsNamesRecursive($element, $names);
    }
  }

  /**
   * Validate translated form are not altered.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateTranslation() {
    if (!$this->hasTranslation() || !$this->originalInputs) {
      return NULL;
    }

    $messages = [];
    $this->validateTranslationInputs($messages, $this->inputs, $this->originalInputs);
    if ($messages) {
      $t_args = [
        ':translation_href' => $this->yamlform->toUrl('config-translation-overview')->toString(),
        '%title' => $this->yamlform->label(),
      ];
      $build = [
        'title' => [
          '#markup' => $this->t('The %title form has <a href=":translation_href">translations</a> and its elements and properties can not be changed.', $t_args),
        ],
        'items' => [
          '#theme' => 'item_list',
          '#items' => $messages,
        ],
      ];
      return \Drupal::service('renderer')->renderPlain($build);
    }
    else {
      return NULL;
    }
  }

  /**
   * Loop inputs and original inputs and make sure that nothing has changed.
   *
   * @param array $messages
   *   Array of missing inputs.
   * @param array $inputs
   *   The updated YAML form inputs.
   * @param array $original_inputs
   *   The original YAML form inputs.
   */
  public function validateTranslationInputs(array &$messages, array $inputs, array $original_inputs, $path = '') {
    if ($items = array_diff(array_keys($original_inputs), array_keys($inputs))) {
      foreach ($items as $item) {
        $t_args = [
          '%name' => $path . $item,
          '@type' => ($item[0] == '#') ? $this->t('property') : $this->t('element'),
        ];
        $messages[] = $this->t('The %name @type can not be removed.', $t_args);
      }
    }
    if ($items = array_diff(array_keys($inputs), array_keys($original_inputs))) {
      foreach ($items as $item) {
        $t_args = [
          '%name' => $path . $item,
          '@type' => ($item[0] == '#') ? $this->t('property') : $this->t('element'),
        ];
        $messages[] = $this->t('The %name @type can not be added.', $t_args);
      }
    }

    foreach (array_keys($original_inputs) as $key) {
      if (!is_array($original_inputs[$key]) || !isset($inputs[$key]) || !is_array($inputs[$key])) {
        continue;
      }
      $this->validateTranslationInputs($messages, $inputs[$key], $original_inputs[$key], $path . $key . '.');
    }
  }

  /**
   * Determine if the current YAML form is translated.
   *
   * @return bool
   *   TRUE if the current YAML form is translated.
   */
  protected function hasTranslation() {
    if (!\Drupal::moduleHandler()->moduleExists('locale')) {
      return FALSE;
    }
    /** @var \Drupal\locale\LocaleConfigManager $local_config_manager */
    $local_config_manager = \Drupal::service('locale.config_manager');
    $languages = \Drupal::languageManager()->getLanguages();
    foreach ($languages as $langcode => $language) {
      if ($local_config_manager->hasTranslation('yamlform.yamlform.' . $this->yamlform->id(), $langcode)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Validate that inputs are a valid render array.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   *
   * @see \Drupal\Core\Entity\EntityFormBuilder
   * @see \Drupal\yamlform\Entity\YamlForm::getSubmissionForm()
   */
  protected function validateRendering() {
    set_error_handler('_yamlform_entity_input_validate_rendering_error_handler');

    try {
      /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
      $entity_manager = \Drupal::service('entity.manager');
      /** @var \Drupal\Core\Form\FormBuilderInterface $form_builder */
      $form_builder = \Drupal::service('form_builder');

      /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
      $yamlform_submission = $entity_manager
        ->getStorage('yamlform_submission')
        ->create(['yamlform' => $this->yamlform]);

      $form_object = $entity_manager->getFormObject('yamlform_submission', 'default');
      $form_object->setEntity($yamlform_submission);
      $form_state = (new FormState())->setFormState([]);
      $form_builder->buildForm($form_object, $form_state);
      $message = NULL;
    }
    catch (\Exception $exception) {
      $message = $exception->getMessage();
    }

    set_error_handler('_drupal_error_handler');

    if ($message) {
      $build = [
        'title' => [
          '#markup' => $this->t('Unable to render inputs, please view the below message and the error log.'),
        ],
        'items' => [
          '#theme' => 'item_list',
          '#items' => [$message],
        ],
      ];
      return \Drupal::service('renderer')->renderPlain($build);
    }

    return $message;
  }

  /**
   * Get the line numbers for given pattern in the YAML form's inputs string.
   *
   * @param string $pattern
   *   A regular expression.
   *
   * @return array
   *   A array of line numbers.
   */
  protected function getLineNumbers($pattern) {
    $lines = explode(PHP_EOL, $this->inputsRaw);
    $line_numbers = [];
    foreach ($lines as $index => $line) {
      if (preg_match($pattern, $line)) {
        $line_numbers[] = ($index + 1);
      }
    }
    return $line_numbers;
  }

}
