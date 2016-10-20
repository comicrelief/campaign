<?php

namespace Drupal\jsonapi\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Validates custom parameter names.
 */
class CustomParameterNames implements AccessInterface {

  /**
   * Validates the JSONAPI parameter names.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(Request $request) {
    $json_api_params = $request->attributes->get('_json_api_params', []);
    if (!$this->validate($json_api_params)) {
      return AccessResult::forbidden();
    }
    return AccessResult::allowed();
  }

  /**
   * Validates the JSONAPI parameters.
   *
   * @param string[] $json_api_params
   *   The JSONAPI parameters.
   *
   * @return bool
   */
  protected function validate(array $json_api_params) {
    $valid = TRUE;

    foreach (array_keys($json_api_params) as $name) {
      if (strpbrk($name, '+,.[]!”#$%&’()*/:;<=>?@^`{}~|')) {
        $valid = FALSE;
        break;
      }

      if (strpbrk($name[0], '-_ ') || strpbrk($name[strlen($name) - 1], '-_ ')) {
        $valid = FALSE;
        break;
      }
    }

    return $valid;
  }

}
