<?php
namespace Drupal\cr_eddirect_ws\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class EdDirectController extends ControllerBase implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    public function lookup($search = "")
    {
        $myService = $this ->container->get('cr_eddirect_ws.eddirect') ->authenticate();
        
        return new JsonResponse(array(0 => 1));
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this ->container = $container;
    }

    public function getContainer()
    {
        return $this ->container;
    }

}