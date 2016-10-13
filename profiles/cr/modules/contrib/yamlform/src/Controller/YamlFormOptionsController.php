<?php

namespace Drupal\yamlform\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\yamlform\Entity\YamlFormOptions;
use Drupal\yamlform\YamlFormInterface;
use Drupal\yamlform\YamlFormOptionsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides route responses for form options.
 */
class YamlFormOptionsController extends ControllerBase {

  /**
   * Returns response for the element autocompletion.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   * @param \Drupal\yamlform\YamlFormInterface $yamlform
   *   A form.
   * @param string $key
   *   Form element key.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions.
   */
  public function autocomplete(Request $request, YamlFormInterface $yamlform, $key) {
    $q = $request->query->get('q');

    // Make sure the current user can access this form.
    if (!$yamlform->access('view')) {
      return new JsonResponse([]);
    }

    // Get the form element element.
    $elements = $yamlform->getElementsInitializedAndFlattened();
    if (!isset($elements[$key])) {
      return new JsonResponse([]);
    }

    // Get the element's form options.
    $element = $elements[$key];
    $element['#options'] = $element['#autocomplete'];
    $options = YamlFormOptions::getElementOptions($element);
    if (empty($options)) {
      return new JsonResponse([]);
    }

    // Filter and convert options to autocomplete matches.
    $matches = [];
    $this->appendOptionsToMatchesRecursive($q, $options, $matches);
    return new JsonResponse($matches);
  }

  /**
   * Append form options to autocomplete matches.
   *
   * @param string $q
   *   String to filter option's label by.
   * @param array $options
   *   An associative array of form options.
   * @param array $matches
   *   An associative array of autocomplete matches.
   */
  protected function appendOptionsToMatchesRecursive($q, array $options, array &$matches) {
    foreach ($options as $value => $label) {
      if (is_array($label)) {
        $this->appendOptionsToMatchesRecursive($q, $label, $matches);
      }
      elseif (stripos($label, $q) !== FALSE) {
        $matches[] = [
          'value' => $value,
          'label' => $label,
        ];
      }
    }
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\yamlform\YamlFormOptionsInterface $yamlform_options
   *   The form options.
   *
   * @return string
   *   The form options label as a render array.
   */
  public function title(YamlFormOptionsInterface $yamlform_options) {
    return $yamlform_options->label();
  }

}
