<?php
/**
 * @file
 * Contains \Drupal\metatag\Plugin\metatag\Tag\Referrer.
 */

namespace Drupal\metatag\Plugin\metatag\Tag;

/**
 * The basic "Referrer policy" meta tag.
 *
 * @MetatagTag(
 *   id = "referrer",
 *   label = @Translation("Referrer policy"),
 *   description = @Translation("Indicate to search engines and other page scrapers whether or not links should be followed. See <a href='http://w3c.github.io/webappsec/specs/referrer-policy/'>the W3C specifications</a> for further details."),
 *   name = "referrer",
 *   group = "advanced",
 *   weight = 5,
 *   type = "label",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class Referrer extends MetaNameBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $element = array()) {
    $form = array(
      '#type' => 'select',
      '#title' => $this->label(),
      '#description' => $this->description(),
      '#options' => array(
        'no-referrer' => t('No Referrer'),
        'origin' => t('Origin'),
        'no-referrer-when-downgrade' => t('No Referrer When Downgrade'),
        'origin-when-cross-origin' => t('Origin When Cross-Origin'),
        'unsafe-url' => t('Unsafe URL'),
      ),
      '#default_value' => $this->value(),
      '#required' => isset($element['#required']) ? $element['#required'] : FALSE,
      '#element_validate' => array(array(get_class($this), 'validateTag')),
    );

    return $form;
  }

}
