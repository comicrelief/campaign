<?php

/**
 * @file
 * Contains \Drupal\yamlform\YamlFormSubmissionViewBuilder.
 */

namespace Drupal\yamlform;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Utility\Token;
use Drupal\yamlform\Plugin\YamlFormElement\ContainerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Render controller for YAML form submissions.
 */
class YamlFormSubmissionViewBuilder extends EntityViewBuilder {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The token handler.
   *
   * @var \Drupal\Core\Utility\Token $token
   */
  protected $token;

  /**
   * The YAML form handler manager service.
   *
   * @var \Drupal\yamlform\YamlFormHandlerManager
   */
  protected $yamlFormHandlerManager;

  /**
   * The YAML form element manager service.
   *
   * @var \Drupal\yamlform\YamlFormElementManager
   */
  protected $yamlFormElementManager;

  /**
   * Constructs a new YamlFormSubmissionViewBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Utility\Token $token
   *   The token handler.
   * @param \Drupal\yamlform\YamlFormHandlerManager $yamlform_handler_manager
   *   The YAML form handler manager service.
   * @param \Drupal\yamlform\YamlFormElementManager $yamlform_element_manager
   *   The YAML form element manager service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory, Token $token, YamlFormHandlerManager $yamlform_handler_manager, YamlFormElementManager $yamlform_element_manager) {
    parent::__construct($entity_type, $entity_manager, $language_manager);
    $this->configFactory = $config_factory;
    $this->token = $token;
    $this->yamlFormHandlerManager = $yamlform_handler_manager;
    $this->yamlFormElementManager = $yamlform_element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('language_manager'),
      $container->get('config.factory'),
      $container->get('token'),
      $container->get('plugin.manager.yamlform.handler'),
      $container->get('plugin.manager.yamlform.element')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    /** @var \Drupal\node\NodeInterface[] $entities */
    if (empty($entities)) {
      return;
    }

    parent::buildComponents($build, $entities, $displays, $view_mode);

    switch ($view_mode) {
      case 'yaml':
      case 'text':
        foreach ($entities as $id => $entity) {
          // Submission.
          $build[$id]['submission'] = [
            '#theme' => 'yamlform_submission_' . $view_mode,
            '#yamlform_submission' => $entity,
          ];
        }
        break;

      default:
        foreach ($entities as $id => $entity) {
          // Navigation.
          $build[$id]['navigation'] = [
            '#theme' => 'yamlform_submission_navigation',
            '#yamlform_submission' => $entity,
          ];

          // Information.
          $build[$id]['information'] = [
            '#theme' => 'yamlform_submission_information',
            '#yamlform_submission' => $entity,
          ];

          // Submission.
          $build[$id]['submission'] = [
            '#theme' => 'yamlform_submission_html',
            '#yamlform_submission' => $entity,
          ];
        }
        break;

    }
  }

  /**
   * Build element display items from inputs and submitted data.
   *
   * @param array $elements
   *   A render array of form elements.
   * @param array $data
   *   Submission data.
   * @param array $options
   *   - excluded_inputs: An array of inputs to be excluded.
   *   - email: Format element to be send via email.
   * @param string $format
   *   Output format set to html or text.
   *
   * @return array
   *   A render array displaying the submitted values.
   */
  public function buildInputs(array $elements, array $data, array $options = [], $format = 'html') {
    $build_method = 'build' . ucfirst($format);
    $build = [];

    foreach ($elements as $key => $element) {
      if (!is_array($element) || Element::property($key) || !$this->isVisibleElement($element) || isset($options['excluded_inputs'][$key])) {
        continue;
      }

      $element += ['#type' => NULL];
      $yamlform_element = $this->yamlFormElementManager->createInstance($element['#type']);
      if ($yamlform_element instanceof ContainerBase) {
        $children = $this->buildInputs($element, $data, $options, $format);
        if ($children) {
          // Add #first and #last property to $children.
          // This is used to remove return from #last with multiple lines of
          // text.
          // @see yamlform-element-base-text.html.twig
          reset($children);
          $first_key = key($children);
          if (isset($children[$first_key]['#options'])) {
            $children[$first_key]['#options']['first'] = TRUE;
          }

          end($children);
          $last_key = key($children);
          if (isset($children[$last_key]['#options'])) {
            $children[$last_key]['#options']['last'] = TRUE;
          }

          // Build the container.
          $build[$key] = $yamlform_element->$build_method($element, $children, $options);
        }
      }
      elseif (isset($data[$key]) && $data[$key] !== '') {
        $build[$key] = $yamlform_element->$build_method($element, $data[$key], $options);
      }
    }
    return $build;
  }

  /**
   * Determines if an element is visible.
   *
   * Copied from: \Drupal\Core\Render\Element::isVisibleElement
   * but does not hide hidden or value elements.
   *
   * @param array $element
   *   The element to check for visibility.
   *
   * @return bool
   *   TRUE if the element is visible, otherwise FALSE.
   */
  public function isVisibleElement(array $element) {
    return (isset($element['#type']))
      && (!isset($element['#access']) || (($element['#access'] instanceof AccessResultInterface && $element['#access']->isAllowed()) || ($element['#access'] === TRUE)));
  }

}
