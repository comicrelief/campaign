<?php

namespace Drupal\yamlform;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Form\FormState;
use Drupal\Core\Url;
use Drupal\yamlform\Utility\YamlFormArrayHelper;
use Drupal\yamlform\Utility\YamlFormElementHelper;

/**
 * Defines a class to validate form elements.
 */
class YamlFormEntityElementsValidator {

  use StringTranslationTrait;

  /**
   * The form being validated.
   *
   * @var \Drupal\yamlform\YamlFormInterface
   */
  protected $yamlform;

  /**
   * The raw elements value.
   *
   * @var string
   */
  protected $elementsRaw;

  /**
   * The raw original elements value.
   *
   * @var string
   */
  protected $originalElementsRaw;

  /**
   * The parsed elements array.
   *
   * @var array
   */
  protected $elements;

  /**
   * The parsed original elements array.
   *
   * @var array
   */
  protected $originalElements;

  /**
   * Validate form elements.
   *
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A form.
   *
   * @return array|null
   *   An array of error messages or NULL is the elements are valid.
   */
  public function validate(YamlFormInterface $yamlform) {
    $this->yamlform = $yamlform;

    $this->elementsRaw = $yamlform->getElementsRaw();
    $this->originalElementsRaw = $yamlform->getElementsOriginalRaw();

    // Validate required.
    if ($message = $this->validateRequired()) {
      return [$message];
    }
    // Validate contain valid YAML.
    if ($message = $this->validateYaml()) {
      return [$message];
    }

    $this->elements = Yaml::decode($this->elementsRaw);
    $this->originalElements = Yaml::decode($this->originalElementsRaw);

    // Validate elements are an array.
    if ($message  = $this->validateArray()) {
      return [$message];
    }

    // Validate translation.
    if ($message = $this->validateTranslation()) {
      return [$message];
    }

    // Validate duplicate element name.
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

    // Validate hierarchy.
    if ($messages = $this->validateHierarchy()) {
      return $messages;
    }

    // Validate rendering.
    if ($message = $this->validateRendering()) {
      return [$message];
    }

    return NULL;
  }

  /**
   * Validate elements are required.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateRequired() {
    return (empty($this->elementsRaw)) ? $this->t('Elements are required') : NULL;
  }

  /**
   * Validate elements is validate YAML.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateYaml() {
    try {
      Yaml::decode($this->elementsRaw);
      return NULL;
    }
    catch (\Exception $exception) {
      return $this->t('Elements are not valid. @message', ['@message' => $exception->getMessage()]);
    }
  }

  /**
   * Validate elements are an array of elements.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateArray() {
    if (!is_array($this->elements)) {
      return $this->t('Elements are not valid. YAML must contain an associative array of elements.');
    }
    return NULL;
  }

  /**
   * Validate elements does not contain duplicate names.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateDuplicateNames() {
    $duplicate_names = [];
    $this->getDuplicateNamesRecursive($this->elements, $duplicate_names);
    if ($duplicate_names = array_filter($duplicate_names)) {
      $messages = [];
      foreach ($duplicate_names as $duplicate_name => $duplicate_count) {
        $line_numbers = $this->getLineNumbers('/^\s*(["\']?)' . preg_quote($duplicate_name, '/') . '\1\s*:/');
        $t_args = [
          '%name' => $duplicate_name,
          '@lines' => $this->formatPlural(count($line_numbers), $this->t('line'), $this->t('lines')),
          '@line_numbers' => YamlFormArrayHelper::toString($line_numbers),
        ];
        $messages[] = $this->t('Elements contain a duplicate element name %name found on @lines @line_numbers.', $t_args);
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
   * Validate that elements are not using ignored properties.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateProperties() {
    $ignored_properties = YamlFormElementHelper::getIgnoredProperties($this->elements);
    if ($ignored_properties) {
      $messages = [];
      foreach ($ignored_properties as $ignored_property) {
        $line_numbers = $this->getLineNumbers('/^\s*(["\']?)' . preg_quote($ignored_property, '/') . '\1\s*:/');
        $t_args = [
          '%property' => $ignored_property,
          '@lines' => $this->formatPlural(count($line_numbers), $this->t('line'), $this->t('lines')),
          '@line_numbers' => YamlFormArrayHelper::toString($line_numbers),
        ];
        $messages[] = $this->t('Elements contain an unsupported %property property found on @lines @line_numbers.', $t_args);
      }
      return $messages;
    }
    return NULL;
  }

  /**
   * Validate that element are not deleted when the form has submissions.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateSubmissions() {
    if (!$this->yamlform->hasSubmissions()) {
      return NULL;
    }

    $element_keys = [];
    if ($this->elements) {
      $this->getElementKeysRecursive($this->elements, $element_keys);
    }
    $original_element_keys = [];
    if ($this->originalElements) {
      $this->getElementKeysRecursive($this->originalElements, $original_element_keys);
    }
    if ($missing_element_keys = array_diff_key($original_element_keys, $element_keys)) {
      $messages = [];
      foreach ($missing_element_keys as $missing_element_key) {
        // Display an error message with 3 possible approaches to safely
        // deleting or hiding an element.
        $items = [];
        $items[] = $this->t('<a href=":href">Delete all submissions</a> to this form.', [':href' => $this->yamlform->toUrl('results-clear')->toString()]);
        if (\Drupal::moduleHandler()->moduleExists('yamlform_ui')) {
          $items[] = $this->t('<a href=":href">Delete this individual element</a> using the form UI.', [':href' => Url::fromRoute('entity.yamlform_ui.element.delete_form', ['yamlform' => $this->yamlform->id(), 'key' => $missing_element_key])->toString()]);
        }
        else {
          $items[] = $this->t('<a href=":href">Enable the YAML Form UI module</a> and safely delete this element.', [':href' => Url::fromRoute('system.modules_list')->toString()]);
        }
        $items[] = $this->t("Hide this element by setting its <code>'#access'</code> property to <code>false</code>.");
        $build = [
          'message' => [
            '#markup' => $this->t('The %key element can not be removed because the %title form has <a href=":href">results</a>.', ['%title' => $this->yamlform->label(), '%key' => $missing_element_key, ':href' => $this->yamlform->toUrl('results-submissions')->toString()]),
          ],
          'items' => [
            '#theme' => 'item_list',
            '#items' => $items,
          ],
        ];
        $messages[] = \Drupal::service('renderer')->renderPlain($build);
      }
      return $messages;
    }

    return NULL;
  }

  /**
   * Recurse through elements and collect an associative array of deleted element names.
   *
   * @param array $elements
   *   An array of elements.
   * @param array $names
   *   An array tracking deleted element names.
   */
  protected function getElementKeysRecursive(array $elements, array &$names) {
    foreach ($elements as $key => &$element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }
      if (isset($element['#type'])) {
        $names[$key] = $key;
      }
      $this->getElementKeysRecursive($element, $names);
    }
  }

  /**
   * Validate element hierarchy.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateHierarchy() {
    $elements = $this->yamlform->getElementsInitializedAndFlattened();
    $messages = [];
    foreach ($elements as $key => $element) {
      /** @var \Drupal\yamlform\YamlFormElementManagerInterface $element_manager */
      $element_manager = \Drupal::service('plugin.manager.yamlform.element');
      $plugin_id = $element_manager->getElementPluginId($element);
      /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
      $yamlform_element = $element_manager->createInstance($plugin_id, $element);

      $t_args = [
        '%title' => (!empty($element['#title'])) ? $element['#title'] : $key,
        '@type' => str_replace('yamlform_', '', $plugin_id),
      ];
      if ($yamlform_element->isRoot($element) && !empty($element['#yamlform_parent_key'])) {
        $messages[] = $this->t('The %title (@type) is a root element that can not be used as child to another element', $t_args);
      }
      elseif (!$yamlform_element->isContainer($element) && !empty($element['#yamlform_children'])) {
        $messages[] = $this->t('The %title (@type) is a form element that can not have any child elements.', $t_args);
      }
    }
    return $messages;
  }

  /**
   * Validate translated form are not altered.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   */
  protected function validateTranslation() {
    if (!$this->yamlform->hasTranslations() || !$this->originalElements) {
      return NULL;
    }

    $messages = [];
    $this->validateTranslationElements($messages, $this->elements, $this->originalElements);
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
   * Loop elements and original elements and make sure that nothing has changed.
   *
   * @param array $messages
   *   Array of missing elements.
   * @param array $elements
   *   The updated form elements.
   * @param array $elements_original
   *   The original form elements.
   */
  public function validateTranslationElements(array &$messages, array $elements, array $elements_original, $path = '') {
    if ($items = array_diff(array_keys($elements_original), array_keys($elements))) {
      foreach ($items as $item) {
        $t_args = [
          '%name' => $path . $item,
          '@type' => ($item[0] == '#') ? $this->t('property') : $this->t('element'),
        ];
        $messages[] = $this->t('The %name @type can not be removed.', $t_args);
      }
    }
    if ($items = array_diff(array_keys($elements), array_keys($elements_original))) {
      foreach ($items as $item) {
        $t_args = [
          '%name' => $path . $item,
          '@type' => ($item[0] == '#') ? $this->t('property') : $this->t('element'),
        ];
        $messages[] = $this->t('The %name @type can not be added.', $t_args);
      }
    }

    foreach (array_keys($elements_original) as $key) {
      if (!is_array($elements_original[$key]) || !isset($elements[$key]) || !is_array($elements[$key])) {
        continue;
      }
      $this->validateTranslationElements($messages, $elements[$key], $elements_original[$key], $path . $key . '.');
    }
  }

  /**
   * Validate that elements are a valid render array.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   If not valid an error message.
   *
   * @see \Drupal\Core\Entity\EntityFormBuilder
   * @see \Drupal\yamlform\Entity\YamlForm::getSubmissionForm()
   */
  protected function validateRendering() {
    set_error_handler('_yamlform_entity_element_validate_rendering_error_handler');

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
          '#markup' => $this->t('Unable to render elements, please view the below message and the error log.'),
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
   * Get the line numbers for given pattern in the form's elements string.
   *
   * @param string $pattern
   *   A regular expression.
   *
   * @return array
   *   An array of line numbers.
   */
  protected function getLineNumbers($pattern) {
    $lines = explode(PHP_EOL, $this->elementsRaw);
    $line_numbers = [];
    foreach ($lines as $index => $line) {
      if (preg_match($pattern, $line)) {
        $line_numbers[] = ($index + 1);
      }
    }
    return $line_numbers;
  }

}
