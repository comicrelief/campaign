<?php

namespace Drupal\yamlform;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Render controller for form submissions.
 */
class YamlFormSubmissionViewBuilder extends EntityViewBuilder implements YamlFormSubmissionViewBuilderInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The token handler.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Form request handler.
   *
   * @var \Drupal\yamlform\YamlFormRequestInterface
   */
  protected $requestManager;

  /**
   * The form handler manager service.
   *
   * @var \Drupal\yamlform\YamlFormHandlerManagerInterface
   */
  protected $handlerManager;

  /**
   * The form element manager service.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

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
   * @param \Drupal\yamlform\YamlFormRequestInterface $yamlform_request
   *   The form request handler.
   * @param \Drupal\yamlform\YamlFormHandlerManagerInterface $handler_manager
   *   The form handler manager service.
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $element_manager
   *   The form element manager service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory, Token $token, YamlFormRequestInterface $yamlform_request, YamlFormHandlerManagerInterface $handler_manager, YamlFormElementManagerInterface $element_manager) {
    parent::__construct($entity_type, $entity_manager, $language_manager);
    $this->configFactory = $config_factory;
    $this->token = $token;
    $this->requestManager = $yamlform_request;
    $this->handlerManager = $handler_manager;
    $this->elementManager = $element_manager;
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
      $container->get('yamlform.request'),
      $container->get('plugin.manager.yamlform.handler'),
      $container->get('plugin.manager.yamlform.element')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface[] $entities */
    /** @var \Drupal\yamlform\YamlFormSubmissionInterface $yamlform_submission */
    if (empty($entities)) {
      return;
    }
    $source_entity = $this->requestManager->getCurrentSourceEntity('yamlform_submission');
    parent::buildComponents($build, $entities, $displays, $view_mode);

    // If the view mode is default then display the HTML version.
    if ($view_mode == 'default') {
      $view_mode = 'html';
    }

    // Build submission display.
    foreach ($entities as $id => $yamlform_submission) {
      $build[$id]['submission'] = [
        '#theme' => 'yamlform_submission_' . $view_mode,
        '#yamlform_submission' => $yamlform_submission,
        '#source_entity' => $source_entity,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildElements(array $elements, array $data, array $options = [], $format = 'html') {
    $build_method = 'build' . ucfirst($format);
    $build = [];

    foreach ($elements as $key => $element) {
      if (!is_array($element) || Element::property($key) || !$this->isVisibleElement($element) || isset($options['excluded_elements'][$key])) {
        continue;
      }

      $plugin_id = $this->elementManager->getElementPluginId($element);
      /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
      $yamlform_element = $this->elementManager->createInstance($plugin_id);
      if ($yamlform_element->isContainer($element)) {
        $children = $this->buildElements($element, $data, $options, $format);
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
        }
        // Build the container but make sure it is not empty. Containers
        // (ie details, fieldsets, etc...) without children will be empty
        // but markup should always be rendered.
        if ($build_container = $yamlform_element->$build_method($element, $children, $options)) {
          $build[$key] = $build_container;
        }
      }
      else {
        $value = isset($data[$key]) ? $data[$key] : NULL;
        if ($build_element = $yamlform_element->$build_method($element, $value, $options)) {
          $build[$key] = $build_element;
        }
      }
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildTable(array $elements, array $data, array $options = []) {
    $rows = [];
    foreach ($elements as $key => $element) {
      if (isset($options['excluded_elements'][$key])) {
        continue;
      }

      $plugin_id = $this->elementManager->getElementPluginId($element);
      /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
      $yamlform_element = $this->elementManager->createInstance($plugin_id);

      $title = $element['#admin_title'] ?: $element['#title'] ?: '(' . $key . ')';
      $value = (isset($data[$key])) ? $yamlform_element->formatHtml($element, $data[$key], $options) : '';
      $rows[] = [
        [
          'header' => TRUE,
          'data' => $title,
        ],
        [
          'data' => (is_string($value)) ? ['#markup' => $value] : $value,
        ],
      ];
    }

    return [
      '#type' => 'table',
      '#rows' => $rows,
      '#attributes' => [
        'class' => ['yamlform-submission__table'],
      ],
    ];
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
  protected function isVisibleElement(array $element) {
    return (!isset($element['#access']) || (($element['#access'] instanceof AccessResultInterface && $element['#access']->isAllowed()) || ($element['#access'] === TRUE)));
  }

}
