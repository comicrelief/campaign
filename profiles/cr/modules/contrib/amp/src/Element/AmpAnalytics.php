<?php

/**
 * @file
 * Contains \Drupal\amp\Element\AmpAnalytics.
 */

namespace Drupal\amp\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for amp-analytics.
 *
 * By default, this element sets #theme so that the 'amp_analytics' theme hook
 * is used for rendering, and attaches the js needed for the amp-analytics
 * component.
 *
 * Properties:
 * - #account: An array with iframe details. See template_preprocess_amp_iframe()
 *   for documentation of the properties in this array.
 *
 * @RenderElement("amp_analytics")
 */
class AmpAnalytics extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return array(
      '#account' => NULL,
      '#attributes' => [],
      '#pre_render' => array(
        array($class, 'preRenderAnalytics'),
      ),
      '#theme' => 'amp_analytics',
    );
  }

  /**
   * Pre-render callback: Attaches the amp-analytics library.
   */
  public static function preRenderAnalytics($element) {
    $element['#attached']['library'][] = 'amp/amp.analytics';
    return $element;
  }
}
