<?php

namespace Drupal\cr_testing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AppLinkController
 *
 * @package Drupal\cr_testing\Controller
 */
class DefaultController extends ControllerBase
{

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function index() {
        return new JsonResponse([], 500);
    }
}
