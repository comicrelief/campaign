<?php

namespace Drupal\yamlform\Plugin\YamlFormElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\yamlform\Element\YamlFormRating as YamlFormRatingElement;

/**
 * Provides a 'rating' element.
 *
 * @YamlFormElement(
 *   id = "yamlform_rating",
 *   label = @Translation("Rating"),
 *   category = @Translation("Advanced elements"),
 * )
 */
class YamlFormRating extends Range {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
      'default_value' => 0,
      'min' => 0,
      'max' => 5,
      'step' => 1,
      'star_size' => 'medium',
      'reset' => FALSE,
    ] + parent::getDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  public function formatHtml(array &$element, $value, array $options = []) {
    $format = $this->getFormat($element);

    switch ($format) {
      case 'star':
        // Always return the raw value when the rating widget is included in an
        // email.
        if (!empty($options['email'])) {
          return parent::formatText($element, $value, $options);
        }

        $build = [
          '#value' => $value,
          '#readonly' => TRUE,
        ] + $element;
        return YamlFormRatingElement::buildRateIt($build);

      default:
        return parent::formatHtml($element, $value, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultFormat() {
    return 'star';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormats() {
    return parent::getFormats() + [
      'star' => $this->t('Star'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['rating'] = [
      '#type' => 'details',
      '#title' => $this->t('Rating settings'),
      '#open' => FALSE,
    ];
    $form['rating']['star_size'] = [
      '#type' => 'select',
      '#title' => $this->t('Star size'),
      '#options' => [
        'small' => $this->t('Small (@size)', ['@size' => '16px']),
        'medium' => $this->t('Medium (@size)', ['@size' => '24px']),
        'large' => $this->t('Large (@size)', ['@size' => '32px']),
      ],
      '#required' => TRUE,
    ];
    $form['rating']['reset'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show reset button'),
      '#description' => $this->t('If checked, a reset button will be placed before the rating element.'),
      '#return_value' => TRUE,
    ];
    return $form;
  }

}
