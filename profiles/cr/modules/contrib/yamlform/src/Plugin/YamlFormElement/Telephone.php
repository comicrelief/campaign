<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

/**
 * Provides a 'tel' element.
 *
 * @YamlFormElement(
 *   id = "tel",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!Tel.php/class/Tel",
 *   label = @Translation("Telephone"),
 *   category = @Translation("Advanced elements"),
 * )
 */
class Telephone extends TextBase {

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    if (empty($value)) {
      return '';
    }

    $format = $this->getFormat($element);
    switch ($format) {
      case 'link':
        // Issue #2484693: Telephone Link fied formatter breaks Drupal with 5
        // digits or less in the number
        // return [
        //  '#type' => 'link',
        //  '#title' => $value,
        //  '#url' => \Drupal::pathValidator()->getUrlIfValid('tel:' . $value),
        // ];
        // Workaround: Manually build a static HTML link.
        $t_args = [':tel' => 'tel:' . $value, '@tel' => $value];
        return t('<a href=":tel">@tel</a>', $t_args);

      default:
        return parent::formatHtml($element, $value, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'link';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'link' => $this->t('Link'),
    ];
  }

}
