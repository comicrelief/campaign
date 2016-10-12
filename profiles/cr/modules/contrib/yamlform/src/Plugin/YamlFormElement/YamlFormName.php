<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormState;
use Drupal\Core\Render\Element;
use Drupal\yamlform\Element\YamlFormName as YamlFormNameElement;

/**
 * Provides an 'name' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_name",
 *   label = @Translation("Name"),
 *   category = @Translation("Composite elements"),
 *   multiline = TRUE,
 *   composite = TRUE,
 * )
 */
class YamlFormName extends YamlFormCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function getCompositeElements() {
    return YamlFormNameElement::getCompositeElements();
  }

  /**
   * {@inheritdoc}
   */
  protected function getInitializedCompositeElement(array &$element) {
    $form_state = new FormState();
    $form_completed = [];
    return YamlFormNameElement::processYamlFormComposite($element, $form_state, $form_completed);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatLines(array $element, array $value) {
    $name_parts = [];
    $composite_elements = $this->getCompositeElements();
    foreach (Element::children($composite_elements) as $name_part) {
      if (!empty($value[$name_part])) {
        $delimiter = (in_array($name_part, ['suffix', 'degree'])) ? ', ' : ' ';
        $name_parts[] = $delimiter . $value[$name_part];
      }
    }

    return [
      'name' => trim(implode('', $name_parts)),
    ];
  }

}
