<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\yamlform\YamlFormElementBase;

/**
 * Provides a 'language_select' element.
 *
 * @YamlFormElement(
 *   id = "language_select",
 *   api = "https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Render!Element!LanguageSelect.php/class/LanguageSelect",
 *   label = @Translation("Language select"),
 *   hidden = TRUE,
 * )
 */
class LanguageSelect extends YamlFormElementBase {

  /**
   * {@inheritdoc}
   */
  public function formatText(array &$element, $value, array $options = []) {
    $language = \Drupal::languageManager()->getLanguage($value);
    $format = $this->getFormat($element);
    switch ($format) {
      case 'langcode':
        return $language->getId();

      case 'language':
        return $language->getName();

      case 'text':
      default:
        // Use `sprintf` instead of FormattableMarkup because we really just
        // want a basic string.
        return sprintf('%s (%s)', $language->getName(), $language->getId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'text';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'text' => $this->t('Text'),
      'langcode' => $this->t('Langcode'),
      'language' => $this->t('Language'),
    ];
  }

}
