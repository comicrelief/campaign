<?php

namespace Drupal\yamlform\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\Core\Url;
use Drupal\yamlform\Utility\YamlFormElementHelper;
use Drupal\yamlform\Utility\YamlFormReflectionHelper;
use Drupal\yamlform\YamlFormElementManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for all form elements.
 */
class YamlFormPluginElementController extends ControllerBase {

  /**
   * A element info manager.
   *
   * @var \Drupal\Core\Render\ElementInfoManagerInterface
   */
  protected $elementInfo;

  /**
   * A form element plugin manager.
   *
   * @var \Drupal\yamlform\YamlFormElementManagerInterface
   */
  protected $elementManager;

  /**
   * Constructs a YamlFormPluginBaseController object.
   *
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $element_info
   *   A element info plugin manager.
   * @param \Drupal\yamlform\YamlFormElementManagerInterface $element_manager
   *   A form element plugin manager.
   */
  public function __construct(ElementInfoManagerInterface $element_info, YamlFormElementManagerInterface $element_manager) {
    $this->elementInfo = $element_info;
    $this->elementManager = $element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.element_info'),
      $container->get('plugin.manager.yamlform.element')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function index() {
    $yamlform_form_element_rows = [];
    $element_rows = [];

    $default_properties = [
      '#title',
      '#description',
      '#required',
      '#default_value',
      '#title_display',
      '#description_display',
      '#prefix',
      '#suffix',
      '#field_prefix',
      '#field_suffix',
      '#private',
      '#unique',
      '#format',
    ];
    $default_properties = array_combine($default_properties, $default_properties);

    // Test element is only enabled if the YAML Form Devel and UI module are
    // enabled.
    $test_element_enabled = (\Drupal::moduleHandler()->moduleExists('yamlform_devel') && \Drupal::moduleHandler()->moduleExists('yamlform_ui')) ? TRUE : FALSE;

    // Define a default element used to get default properties.
    $element = ['#type' => 'element'];

    $element_plugin_definitions = $this->elementInfo->getDefinitions();
    foreach ($element_plugin_definitions as $element_plugin_id => $element_plugin_definition) {
      if ($this->elementManager->hasDefinition($element_plugin_id)) {

        /** @var \Drupal\yamlform\YamlFormElementInterface $yamlform_element */
        $yamlform_element = $this->elementManager->createInstance($element_plugin_id);
        $yamlform_element_plugin_definition = $this->elementManager->getDefinition($element_plugin_id);
        $yamlform_element_info = $yamlform_element->getInfo();

        $parent_classes = YamlFormReflectionHelper::getParentClasses($yamlform_element, 'YamlFormElementBase');

        $default_format = $yamlform_element->getDefaultFormat();
        $format_names = array_keys($yamlform_element->getFormats());
        $formats = array_combine($format_names, $format_names);
        if (isset($formats[$default_format])) {
          $formats[$default_format] = '<b>' . $formats[$default_format] . '</b>';
        }

        $related_types = $yamlform_element->getRelatedTypes($element);

        $yamlform_info_definitions = [
          'input' => $yamlform_element->isInput($element),
          'container' => $yamlform_element->isContainer($element),
          'root' => $yamlform_element->isRoot($element),
          'hidden' => $yamlform_element->isHidden($element),
          'multiline' => $yamlform_element->isMultiline($element),
          'multiple' => $yamlform_element->hasMultipleValues($element),
          'states_wrapper' => $yamlform_element_plugin_definition['states_wrapper'],
        ];
        $yamlform_info = [];
        foreach ($yamlform_info_definitions as $key => $value) {
          $yamlform_info[] = '<b>' . $key . '</b>: ' . ($value ? $this->t('Yes') : $this->t('No'));
        }

        $element_info_definitions = [
          'input' => (empty($yamlform_element_info['#input'])) ? $this->t('No') : $this->t('Yes'),
          'theme' => (isset($yamlform_element_info['#theme'])) ? $yamlform_element_info['#theme'] : 'N/A',
          'theme_wrappers' => (isset($yamlform_element_info['#theme_wrappers'])) ? implode('; ', $yamlform_element_info['#theme_wrappers']) : 'N/A',
        ];
        $element_info = [];
        foreach ($element_info_definitions as $key => $value) {
          $element_info[] = '<b>' . $key . '</b>: ' . $value;
        }

        $properties = array_keys(YamlFormElementHelper::addPrefix($yamlform_element->getDefaultProperties()));
        foreach ($properties as &$property) {
          if (!isset($default_properties[$property])) {
            $property = '<b>' . $property . '</b>';
          }
        }
        if (count($properties) >= 20) {
          $properties = array_slice($properties, 0, 20) + ['...' => '...'];
        }
        $operations = [];
        if ($test_element_enabled) {
          $operations['test'] = [
            'title' => $this->t('Test'),
            'url' => new Url('yamlform.element_plugins.test', ['type' => $element_plugin_id]),
          ];
        }
        if ($api_url = $yamlform_element->getPluginApiUrl()) {
          $operations['documentation'] = [
            'title' => $this->t('API Docs'),
            'url' => $api_url,
          ];
        }
        $yamlform_form_element_rows[$element_plugin_id] = [
          'data' => [
            new FormattableMarkup('<div class="yamlform-form-filter-text-source">@id</div>', ['@id' => $element_plugin_id]),
            $yamlform_element->getPluginLabel(),
            ['data' => ['#markup' => implode('<br/> → ', $parent_classes)], 'nowrap' => 'nowrap'],
            ['data' => ['#markup' => implode('<br/>', $yamlform_info)], 'nowrap' => 'nowrap'],
            ['data' => ['#markup' => implode('<br/>', $element_info)], 'nowrap' => 'nowrap'],
            ['data' => ['#markup' => implode('<br/>', $properties)]],
            $formats ? ['data' => ['#markup' => '• ' . implode('<br/>• ', $formats)], 'nowrap' => 'nowrap'] : '',
            $related_types ? ['data' => ['#markup' => '• ' . implode('<br/>• ', $related_types)], 'nowrap' => 'nowrap'] : '<' . $this->t('none') . '>',
            $element_plugin_definition['provider'],
            $yamlform_element_plugin_definition['provider'],
            $operations ? ['data' => ['#type' => 'operations', '#links' => $operations]] : '',
          ],
        ];
      }
      else {
        $element_rows[$element_plugin_id] = [
          $element_plugin_id,
          $element_plugin_definition['provider'],
        ];
      }
    }

    $build = [];

    $build['filter'] = [
      '#type' => 'search',
      '#title' => $this->t('Filter'),
      '#title_display' => 'invisible',
      '#size' => 30,
      '#placeholder' => $this->t('Filter by element name'),
      '#attributes' => [
        'class' => ['yamlform-form-filter-text'],
        'data-element' => '.yamlform-element-plugin',
        'title' => $this->t('Enter a part of the handler name to filter by.'),
        'autofocus' => 'autofocus',
      ],
    ];

    ksort($yamlform_form_element_rows);
    $build['yamlform_elements'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Label'),
        $this->t('Class hierarchy'),
        $this->t('YAML Form info'),
        $this->t('Element info'),
        $this->t('Properties'),
        $this->t('Formats'),
        $this->t('Related'),
        $this->t('Provided by'),
        $this->t('Integrated by'),
        $this->t('Operations'),
      ],
      '#rows' => $yamlform_form_element_rows,
      '#attributes' => [
        'class' => ['yamlform-element-plugin'],
      ],
    ];

    ksort($element_rows);
    $build['elements'] = [
      '#type' => 'details',
      '#title' => $this->t('Additional elements'),
      '#description' => $this->t('Below are elements that available but do not have a YAML Form Element integration plugin.'),
      'table' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Provided by'),
        ],
        '#rows' => $element_rows,
        '#sticky' => TRUE,
      ],
    ];

    $build['#attached']['library'][] = 'yamlform/yamlform.admin';
    $build['#attached']['library'][] = 'yamlform/yamlform.form';

    return $build;
  }

  /**
   * Get a class's name without its namespace.
   *
   * @param string $class
   *   A class.
   *
   * @return string
   *   The class's name without its namespace.
   */
  protected function getClassName($class) {
    $parts = preg_split('#\\\\#', $class);
    return end($parts);
  }

}
