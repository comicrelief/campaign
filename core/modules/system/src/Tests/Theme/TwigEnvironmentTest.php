<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Theme\TwigEnvironmentTest.
 */

namespace Drupal\system\Tests\Theme;

use Drupal\Component\Utility\Html;
use Drupal\Core\Site\Settings;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests the twig environment.
 *
 * @see \Drupal\Core\Template\TwigEnvironment
 * @group Twig
 */
class TwigEnvironmentTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system');

  /**
   * Tests inline templates.
   */
  public function testInlineTemplate() {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');
    /** @var \Drupal\Core\Template\TwigEnvironment $environment */
    $environment = \Drupal::service('twig');
    $this->assertEqual($environment->renderInline('test-no-context'), 'test-no-context');
    $this->assertEqual($environment->renderInline('test-with-context {{ llama }}', array('llama' => 'muuh')), 'test-with-context muuh');

    $element = array();
    $unsafe_string = '<script>alert(\'Danger! High voltage!\');</script>';
    $element['test'] = array(
      '#type' => 'inline_template',
      '#template' => 'test-with-context <label>{{ unsafe_content }}</label>',
      '#context' => array('unsafe_content' => $unsafe_string),
    );
    $this->assertEqual($renderer->renderRoot($element), 'test-with-context <label>' . Html::escape($unsafe_string) . '</label>');

    // Enable twig_auto_reload and twig_debug.
    $settings = Settings::getAll();
    $settings['twig_debug'] = TRUE;
    $settings['twig_auto_reload'] = TRUE;

    new Settings($settings);
    $this->container = $this->kernel->rebuildContainer();
    \Drupal::setContainer($this->container);

    $element = array();
    $element['test'] = array(
      '#type' => 'inline_template',
      '#template' => 'test-with-context {{ llama }}',
      '#context' => array('llama' => 'muuh'),
    );
    $element_copy = $element;
    // Render it twice so that twig caching is triggered.
    $this->assertEqual($renderer->renderRoot($element), 'test-with-context muuh');
    $this->assertEqual($renderer->renderRoot($element_copy), 'test-with-context muuh');

    // Tests caching of inline templates with long content to ensure the
    // generated cache key can be used as a filename.
    $element = [];
    $element['test'] = [
      '#type' => 'inline_template',
      '#template' => 'Llamas sometimes spit and wrestle with their {{ llama }}. Kittens are soft and fuzzy and they sometimes say {{ kitten }}. Flamingos have long legs and they are usually {{ flamingo }}. Pandas eat bamboo and they are {{ panda }}. Giraffes have long necks and long tongues and they eat {{ giraffe }}.',
      '#context' => [
        'llama' => 'necks',
        'kitten' => 'meow',
        'flamingo' => 'pink',
        'panda' => 'bears',
        'giraffe' => 'leaves',
      ],
    ];
    $expected = 'Llamas sometimes spit and wrestle with their necks. Kittens are soft and fuzzy and they sometimes say meow. Flamingos have long legs and they are usually pink. Pandas eat bamboo and they are bears. Giraffes have long necks and long tongues and they eat leaves.';
    $element_copy = $element;

    // Render it twice so that twig caching is triggered.
    $this->assertEqual($renderer->renderRoot($element), $expected);
    $this->assertEqual($renderer->renderRoot($element_copy), $expected);

    $name = '{# inline_template_start #}' . $element['test']['#template'];
    $hash = $this->container->getParameter('twig_extension_hash');

    $cache = $environment->getCache();
    $class = $environment->getTemplateClass($name);
    $expected = $hash . '_inline-template' . '_' . hash('sha256', $class);
    $this->assertEqual($expected, $cache->generateKey($name, $class));
  }

  /**
   * Tests that exceptions are thrown when a template is not found.
   */
  public function testTemplateNotFoundException() {
    /** @var \Drupal\Core\Template\TwigEnvironment $environment */
    $environment = \Drupal::service('twig');

    try {
      $environment->loadTemplate('this-template-does-not-exist.html.twig')->render(array());
      $this->fail('Did not throw an exception as expected.');
    }
    catch (\Twig_Error_Loader $e) {
      $this->assertTrue(strpos($e->getMessage(), 'Template "this-template-does-not-exist.html.twig" is not defined') === 0);
    }
  }

  /**
   * Ensures that cacheFilename() varies by extensions + deployment identifier.
   */
  public function testCacheFilename() {
    /** @var \Drupal\Core\Template\TwigEnvironment $environment */
    // Note: Later we refetch the twig service in order to bypass its internal
    // static cache.
    $environment = \Drupal::service('twig');

    $original_filename = $environment->getCacheFilename('core/modules/system/templates/container.html.twig');
    \Drupal::getContainer()->set('twig', NULL);

    \Drupal::service('module_installer')->install(['twig_extension_test']);
    $environment = \Drupal::service('twig');
    $new_extension_filename = $environment->getCacheFilename('core/modules/system/templates/container.html.twig');
    \Drupal::getContainer()->set('twig', NULL);

    $this->assertNotEqual($new_extension_filename, $original_filename);
  }

}

