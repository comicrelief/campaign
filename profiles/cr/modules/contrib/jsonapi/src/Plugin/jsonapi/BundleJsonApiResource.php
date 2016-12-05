<?php

namespace Drupal\jsonapi\Plugin\jsonapi;

use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\jsonapi\Plugin\JsonApiResourceBase;
use Drupal\jsonapi\Plugin\JsonApiResourceInterface;

/**
 * Class BundleJsonApiResource
 *
 * Represents bundles as JSON API resources.
 *
 * @see \Drupal\jsonapi\Plugin\Deriver\ResourceDeriver
 *
 * @JsonApiResource(
 *   id = "bundle",
 *   label = @Translation("Bundle"),
 *   deriver = "Drupal\jsonapi\Plugin\Deriver\ResourceDeriver",
 * )
 *
 * @package Drupal\jsonapi\Plugin\jsonapi
 */
class BundleJsonApiResource extends ContextAwarePluginBase implements JsonApiResourceInterface {

  // TODO: The plugin will contain a factory to a field mapper class.

}
