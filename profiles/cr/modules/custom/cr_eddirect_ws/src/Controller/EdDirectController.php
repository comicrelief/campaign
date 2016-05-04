<?php
namespace Drupal\cr_eddirect_ws\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class EdDirectController extends ControllerBase implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @param string $searchString
     * @return JsonResponse
     */
    public function lookup($searchString = "")
    {
        $edDirectService = $this ->container->get('cr_eddirect_ws.eddirect');

        $postcodeRegex = '/
                ^(([A-Z]{1,2})      # one or two letters
                ([0-9]{1,2}[A-Z]?)) # one or two numbers, optional
                \s?                 # space, optional
                (([0-9]{1})         # one number
                ([A-Z]{1,2})?)      # one or two letters, optional
                ?$
                /ix';
        
        if (preg_match($postcodeRegex, $searchString)) {
            $searchResults = $edDirectService ->searchByPostcode($searchString);
        } elseif (strlen($searchString) > 2) {
            $searchResults = $edDirectService ->searchByName($searchString);
        } else {
            $searchResults = array();
        }

        return new JsonResponse(array("results" => $searchResults, 'count' => count($searchResults)));
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this ->container = $container;
    }
}