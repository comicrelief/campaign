<?php

namespace Drupal\yamlform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\yamlform\Utility\YamlFormDialogHelper;
use Drupal\yamlform\YamlFormHandlerInterface;
use Drupal\yamlform\YamlFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for all form handlers.
 */
class YamlFormPluginHandlerController extends ControllerBase {

  /**
   * A form handler plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Constructs a YamlFormPluginBaseController object.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   A form handler plugin manager.
   */
  public function __construct(PluginManagerInterface $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.yamlform.handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function index() {
    $definitions = $this->pluginManager->getDefinitions();
    $definitions = $this->pluginManager->getSortedDefinitions($definitions);

    $rows = [];
    foreach ($definitions as $plugin_id => $definition) {
      $rows[$plugin_id] = [
        $plugin_id,
        $definition['label'],
        $definition['description'],
        $definition['category'],
        ($definition['cardinality'] == -1) ? $this->t('Unlimited') : $definition['cardinality'],
        $definition['results'] ? $this->t('Processed') : $this->t('Ignored'),
        $definition['provider'],
      ];
    }

    ksort($rows);
    return [
      '#type' => 'table',
      '#header' => [
        $this->t('ID'),
        $this->t('Label'),
        $this->t('Description'),
        $this->t('Category'),
        $this->t('Cardinality'),
        $this->t('Results'),
        $this->t('Provided by'),
      ],
      '#rows' => $rows,
      '#sticky' => TRUE,
    ];
  }

  /**
   * Shows a list of form handlers that can be added to a form.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A form.
   *
   * @return array
   *   A render array as expected by the renderer.
   */
  public function listHandlers(Request $request, YamlFormInterface $yamlform) {
    $headers = [
      ['data' => $this->t('Handler'), 'width' => '20%'],
      ['data' => $this->t('Description'), 'width' => '40%'],
      ['data' => $this->t('Category'), 'width' => '20%'],
      ['data' => $this->t('Operations'), 'width' => '20%'],
    ];

    $definitions = $this->pluginManager->getDefinitions();
    $definitions = $this->pluginManager->getSortedDefinitions($definitions);

    $rows = [];
    foreach ($definitions as $plugin_id => $definition) {
      // Skip email handler which has dedicated button.
      if ($plugin_id == 'email') {
        continue;
      }

      // Check cardinality.
      $cardinality = $definition['cardinality'];
      $is_cardinality_unlimited = ($cardinality == YamlFormHandlerInterface::CARDINALITY_UNLIMITED);
      $is_cardinality_reached = ($yamlform->getHandlers($plugin_id)->count() >= $cardinality);
      if (!$is_cardinality_unlimited && $is_cardinality_reached) {
        continue;
      }

      $row = [];
      $row['title']['data'] = [
        '#type' => 'inline_template',
        '#template' => '<div class="yamlform-form-filter-text-source">{{ label }}</div>',
        '#context' => [
          'label' => $definition['label'],
        ],
      ];
      $row['description'] = [
        'data' => [
          '#markup' => $definition['description'],
        ],
      ];
      $row['category'] = $definition['category'];
      $links['add'] = [
        'title' => $this->t('Add handler'),
        'url' => Url::fromRoute('entity.yamlform.handler.add_form', ['yamlform' => $yamlform->id(), 'yamlform_handler' => $plugin_id]),
        'attributes' => YamlFormDialogHelper::getModalDialogAttributes(800),
      ];
      $row['operations']['data'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];
      $rows[] = $row;
    }

    $build['#attached']['library'][] = 'yamlform/yamlform.form';

    $build['filter'] = [
      '#type' => 'search',
      '#title' => $this->t('Filter'),
      '#title_display' => 'invisible',
      '#size' => 30,
      '#placeholder' => $this->t('Filter by handler name'),
      '#attributes' => [
        'class' => ['yamlform-form-filter-text'],
        'data-element' => '.yamlform-handler-add-table',
        'title' => $this->t('Enter a part of the handler name to filter by.'),
        'autofocus' => 'autofocus',
      ],
    ];

    $build['handlers'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No handler available.'),
      '#attributes' => [
        'class' => ['yamlform-handler-add-table'],
      ],
    ];

    return $build;
  }

}
