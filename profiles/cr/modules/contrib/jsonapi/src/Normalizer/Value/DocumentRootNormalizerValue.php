<?php

namespace Drupal\jsonapi\Normalizer\Value;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\jsonapi\RequestCacheabilityDependency;

/**
 * Class DocumentRootNormalizerValue.
 *
 * @package Drupal\jsonapi\Normalizer\Value
 */
class DocumentRootNormalizerValue implements DocumentRootNormalizerValueInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * The values.
   *
   * @param array
   */
  protected $values;

  /**
   * The includes.
   *
   * @param array
   */
  protected $includes;

  /**
   * The resource path.
   *
   * @param array
   */
  protected $context;

  /**
   * Is collection?
   *
   * @param bool
   */
  protected $isCollection;

  /**
   * The link manager.
   *
   * @var \Drupal\jsonapi\LinkManager\LinkManagerInterface
   */
  protected $linkManager;

  /**
   * The link context.
   *
   * @var array
   */
  protected $linkContext;

  /**
   * Instantiates a DocumentRootNormalizerValue object.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $values
   *   The data to normalize. It can be either a straight up entity or a
   *   collection of entities.
   * @param array $context
   *   The context.
   * @param bool $is_collection
   *   TRUE if this is a serialization for a list.
   * @param array $link_context
   *   All the objects and variables needed to generate the links for this
   *   relationship.
   */
  public function __construct(array $values, array $context, $is_collection = FALSE, array $link_context) {
    $this->values = $values;
    array_walk($values, function ($value) {
      $this->addCacheableDependency($value);
    });
    // Make sure that different sparse fieldsets are cached differently.
    $this->addCacheableDependency(new RequestCacheabilityDependency());

    $this->context = $context;
    $this->isCollection = $is_collection;
    $this->linkManager = $link_context['link_manager'];
    // Remove the manager and store the link context.
    unset($link_context['link_manager']);
    $this->linkContext = $link_context;
    // Get an array of arrays of includes.
    $this->includes = array_map(function ($value) {
      return $value->getIncludes();
    }, $values);
    // Flatten the includes.
    $this->includes = array_reduce($this->includes, function ($carry, $includes) {
      array_walk($includes, function ($include) {
        $this->addCacheableDependency($include);
      });
      return array_merge($carry, $includes);
    }, []);
    // Filter the empty values.
    $this->includes = array_filter($this->includes);
  }

  /**
   * {@inheritdoc}
   */
  public function rasterizeValue() {
    // Create the array of normalized fields, starting with the URI.
    $rasterized = ['data' => []];

    foreach ($this->values as $normalizer_value) {
      if ($normalizer_value instanceof HttpExceptionNormalizerValue) {
        $previous_errors = NestedArray::getValue($rasterized, ['meta', 'errors']) ?: [];
        // Add the errors to the pre-existing errors.
        $rasterized['meta']['errors'] = array_merge($previous_errors, $normalizer_value->rasterizeValue());
      }
    else {
        $rasterized['data'][] = $normalizer_value->rasterizeValue();
      }
    }
    $rasterized['data'] = array_filter($rasterized['data']);
    // Deal with the single entity case.
    $rasterized['data'] = $this->isCollection ?
      $rasterized['data'] :
      reset($rasterized['data']);

    // Add the self link.
    if ($this->context['request']) {
      /* @var \Symfony\Component\HttpFoundation\Request $request */
      $request = $this->context['request'];
      $rasterized['links'] = [
        'self' => $this->linkManager->getRequestLink($request),
      ];
      // If this is a collection we need to append the pager links.
      if ($this->isCollection) {
        $rasterized['links'] += $this->linkManager->getPagerLinks($request, $this->linkContext);
      }
    }
    return $rasterized;
  }

  /**
   * Gets a flattened list of includes in all the chain.
   *
   * @return EntityNormalizerValueInterface[]
   *   The array of included relationships.
   */
  public function getIncludes() {
    $nested_includes = array_map(function ($include) {
      return $include->getIncludes();
    }, $this->includes);
    $includes = array_reduce(array_filter($nested_includes), function ($carry, $item) {
      return array_merge($carry, $item);
    }, $this->includes);
    // Make sure we don't output duplicate includes.
    return array_values(array_reduce($includes, function ($unique_includes, $include) {
      $rasterized_include = $include->rasterizeValue();
      $unique_includes[$rasterized_include['data']['type'] . ':' . $rasterized_include['data']['id']] = $include;
      return $unique_includes;
    }, []));
  }

  /**
   * {@inheritdoc}
   */
  public function rasterizeIncludes() {
    // First gather all the includes in the chain.
    return array_map(function ($include) {
      return $include->rasterizeValue();
    }, $this->getIncludes());
  }

}
