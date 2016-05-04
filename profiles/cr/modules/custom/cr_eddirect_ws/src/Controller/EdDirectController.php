<?php
/**
 * @file
 * EdDirect Micro Service Controller.
 */

namespace Drupal\cr_eddirect_ws\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Implementation of Controller.
 *
 * @package Drupal\cr_eddirect_ws
 */
class EdDirectController extends ControllerBase implements ContainerAwareInterface {
  /**
   * DI Container.
   *
   * @var ContainerInterface $container
   */
  private $container;

  /**
   * Attempt lookup via Service.
   *
   * @param string $search
   *   What name/postcode to search for.
   *
   * @return JsonResponse
   *   Return the response in JSON.
   */
  public function lookup($search = "") {

    $ed_direct_service = $this->container->get('cr_eddirect_ws.eddirect');

    $postcode_regex = '/
                ^(([A-Z]{1,2})      # one or two letters
                ([0-9]{1,2}[A-Z]?)) # one or two numbers, optional
                \s?                 # space, optional
                (([0-9]{1})         # one number
                ([A-Z]{1,2})?)      # one or two letters, optional
                ?$
                /ix';

    if (preg_match($postcode_regex, $search)) {
      $search_results = $ed_direct_service->searchByPostcode($search);
    }
    elseif (strlen($search) > 2) {
      $search_results = $ed_direct_service->searchByName($search);
    }
    else {
      $search_results = array();
    }

    return new JsonResponse(array("results" => $search_results, 'count' => count($search_results)));
  }

  /**
   * Set the container service.
   *
   * @param ContainerInterface|null $container
   *    The Container.
   */
  public function setContainer(ContainerInterface $container = NULL) {

    $this->container = $container;
  }

}
