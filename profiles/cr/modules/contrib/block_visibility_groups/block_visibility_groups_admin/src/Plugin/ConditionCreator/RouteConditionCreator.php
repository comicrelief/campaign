<?php

namespace Drupal\block_visibility_groups_admin\Plugin\ConditionCreator;

use Drupal\block_visibility_groups_admin\Plugin\ConditionCreatorBase;

/**
 * Defines the form in-place editor.
 *
 * @ConditionCreator(
 *   id = "route",
 *   label = "Route",
 *   condition_plugin = "request_path"
 * )
 */
class RouteConditionCreator extends ConditionCreatorBase {

  /**
   *
   */
  public function getNewConditionLabel() {
    $current_path = $this->getPathPattern();
    return $this->t('Current path: @path', ['@path' => $current_path]);
  }

  /**
   * @return mixed|string
   */
  protected function getPathPattern() {
    $route = $this->route->getRouteObject();
    $path = $route->getPath();
    $parameters = $route->compile()->getPathVariables();
    foreach ($parameters as $parameter) {
      $path = str_replace('{' . $parameter . '}', '*', $path);
    }
    return $path;
  }

  /**
   *
   */
  public function createConditionElements() {
    $elements = parent::createConditionElements();

    $elements['condition_config'] = [
      '#type' => 'value',
      '#value' => [
        'pages' => $this->getPathPattern(),
      ],
    ];
    return $elements;
  }

}
