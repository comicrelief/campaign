<?php

/**
 * @file
 * Contains \Drupal\amp\Controller\AMPTestLibrary.
 */

namespace Drupal\amp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\amp\Service\AMPService;

/**
 * Class AMPTestLibrary.
 *
 * @package Drupal\amp\Controller
 */
class AMPTestLibrary extends ControllerBase {

  /**
   * Drupal\amp\AMPService definition.
   *
   * @var AMPService
   */
  protected $amp;
  /**
   * {@inheritdoc}
   */
  public function __construct(AMPService $amp_utilities) {
    $this->amp = $amp_utilities->createAMPConverter();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('amp.utilities')
    );
  }

  /**
   * Hello.
   *
   * @return string
   */
  public function hello() {
    $html_header = PHP_EOL . PHP_EOL . 'OUTPUT HTML'. PHP_EOL;
    $html_header .= '-------------' . PHP_EOL;
    $html = '<p><a href="javascript:run();">Run</a></p>' . PHP_EOL .
        '<p><a style="margin: 2px;" href="http://www.cnn.com" target="_parent">CNN</a></p>' . PHP_EOL .
        '<p><a href="http://www.bbcnews.com" target="_blank">BBC</a></p>' . PHP_EOL .
        '<p><INPUT type="submit" value="submit"></p>' . PHP_EOL .
        '<p>This is a <!-- test comment -->sample <div onmouseover="hello();">sample</div> paragraph</p>';

    $this->amp->loadHtml($html);
    $amp_html = htmlspecialchars($this->amp->convertToAmpHtml());
    $original_html = PHP_EOL . PHP_EOL . 'ORIGINAL TEST HTML INPUT'. PHP_EOL;
    $original_html .=                    '-------------------------' . PHP_EOL;
    $original_html .= htmlspecialchars($html);
    return [
        '#type' => 'markup',
        '#markup' => "<h3>The Library is working fine</h3><pre>$html_header $amp_html $original_html" .  $this->amp->warningsHumanHtml() . "</pre>"
    ];
  }

}
