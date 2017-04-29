<?php

/**
 * @file
 * Contains \Drupal\amp\Element\AmpProcessedText.
 */

namespace Drupal\amp\Element;

use Drupal\filter\Element\ProcessedText;
use Lullabot\AMP\AMP;
use Drupal\amp\Service\AMPService;

/**
 * Provides an amp-processed text render element.
 *
 * @RenderElement("amp_processed_text")
 */
class AmpProcessedText extends ProcessedText {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return array(
      '#text' => '',
      '#format' => NULL,
      '#filter_types_to_skip' => array(),
      '#langcode' => '',
      '#pre_render' => array(
        array($class, 'preRenderText'),
        array($class, 'preRenderAmpText'),
      ),
      '#cache' => [
        'tags' => ['amp-warnings'],
        // This should be bubbling up but its not
        // Instead we have to place the #cache setting in src/Controller/ampPage
        // 'context' => ['url.query_args:warnfix'],
      ]
    );
  }

  /**
   * Does the user want to see AMP Library warnings?
   *
   * @return bool
   */
  public static function warningsOn()
  {
    // First check the config if library warnings are on
    $amp_config = self::configFactory()->get('amp.settings');
    if ($amp_config->get('amp_library_warnings_display')) {
      return true;
    }

    // Then check the URL if library warnings are enabled
    /** @var Request $request */
    $request = \Drupal::request();
    $user_wants_amp_library_warnings = $request->get('warnfix');
    if (isset($user_wants_amp_library_warnings)) {
      return true;
    }

    return false;
  }

  /**
   * Pre-render callback: Processes the amp markup and attaches libraries.
   */
  public static function preRenderAmpText($element) {

    /** @var AMPService $amp_service */
    $amp_service = \Drupal::getContainer()->get('amp.utilities');
    /** @var AMP $amp */
    $amp = $amp_service->createAMPConverter();

    $amp->loadHtml($element['#markup']);
    $element['#markup'] = $amp->convertToAmpHtml();
    $warning_message = "<pre>" . $amp->warningsHumanHtml() . "</pre></div>";

    if (self::warningsOn()) {
      $element['#markup'] .= $warning_message;
    }

    if (!empty($amp->getComponentJs())) {
      $element['#attached']['library'] = $amp_service->addComponentLibraries($amp->getComponentJs());
    }

    return $element;
  }
}
