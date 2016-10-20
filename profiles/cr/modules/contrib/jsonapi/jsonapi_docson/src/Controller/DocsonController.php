<?php

namespace Drupal\jsonapi_docson\Controller;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\jsonapi\Plugin\JsonApiResourceManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class DocsonController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The resource plugin manager interface.
   *
   * @var \Drupal\rest\Plugin\Type\ResourcePluginManager
   */
  protected $resourcePluginManager;

  /**
   * Instantiates a Routes object.
   *
   * @param \Drupal\jsonapi\Plugin\JsonApiResourceManager $resource_plugin_manager
   *   The resource manager.
   */
  public function __construct(JsonApiResourceManager $resource_plugin_manager) {
    $this->resourcePluginManager = $resource_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\jsonapi\Plugin\JsonApiResourceManager $resource_plugin_manager */
    $resource_plugin_manager = $container->get('plugin.manager.resource.processor');

    return new static($resource_plugin_manager);
  }

  public function listResources() {
    $build = [
      '#type' => 'table',
      '#header' => [$this->t('Resource'), $this->t('Schema')],
    ];

    foreach ($this->resourcePluginManager->getDefinitions() as $plugin_id => $plugin_definition) {
      if (empty($plugin_definition['enabled'])) {
        continue;
      }
      $partial_path = $plugin_definition['data']['partialPath'];
      $schema_partial_path = $plugin_definition['schema']['partialPath'];
      $route_key_parts = explode(':', $plugin_id, 2);
      $route_key = end($route_key_parts) . '.';
      $entity_type = $plugin_definition['entityType'];

      // @todo its sad that this module needs to know about the different kind
      //   of schemas we expose.
      $build[$partial_path] = [
        ['data' => ['#markup' => $partial_path]],
        ['data' => Link::createFromRoute('Schema:' . $schema_partial_path, 'jsonapi_docson.schema_inspector', ['schema' => Url::fromRoute($route_key . 'schema')->toString(), 'resource_id' => $plugin_id])->toRenderable()],
      ];

      $individual_path = sprintf('%s/{%s}', $partial_path, $entity_type);
      $schema_individual_path = $schema_partial_path . '/individual';
      $build[$individual_path] = [
        ['data' => ['#markup' => $individual_path]],
        ['data' => Link::createFromRoute('Schema:' . $schema_individual_path, 'jsonapi_docson.schema_inspector', ['schema' => Url::fromRoute($route_key . 'individual.schema', [$entity_type => 'individual'])->toString(), 'resource_id' => $plugin_id])->toRenderable()],
      ];
    }

    return $build;
  }

  public function inspectSchema(Request $request) {
    $schema = $request->query->get('schema');
    $resource_id = $request->query->get('resource_id');
    try {
      $plugin_definition = $this->resourcePluginManager->getDefinition($resource_id);
    }
    catch (PluginNotFoundException $e) {
      return NULL;
    }

    $build = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#attributes' => [
        'src' => '/libraries/docson/widget.js',
        'data-schema' => $schema,
      ],
    ];
    if ($plugin_definition) {
      $build['#title'] = $this->t('Schema for "@entity_type/@bundle"', [
        '@entity_type' => $plugin_definition['entityType'],
        '@bundle' => $plugin_definition['bundle'],
      ]);
    }

    return $build;
  }

}
