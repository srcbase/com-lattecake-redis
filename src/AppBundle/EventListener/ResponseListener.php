<?php
/**
 * Created by PhpStorm.
 * User: LatteCake
 * Date: 16/7/25
 * Time: 下午6:35
 * File: ResponseListener.php
 */

namespace AppBundle\EventListener;


use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


class ResponseListener
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var
     */
    protected $controller;

    /**
     * @var
     */
    protected $response;

    /**
     * @var Router
     */
    protected $matcher;

    /**
     * @var Request
     */
    protected $request;

    public function __construct(LoggerInterface $logger, Router $matcher)
    {
        $this->logger = $logger;
        $this->matcher = $matcher;
    }

    /**
     * @param FilterResponseEvent $event
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getResponse() instanceof JsonResponse) {
            $response = json_decode($event->getResponse()->getContent(), true);

            $returnResponse = new JsonResponse($response, 200, ['Access-Control-Allow-Origin' => '*']);
            $event->setResponse($returnResponse);
        } else {
            $response = $event->getResponse()->getStatusCode();
        }

        $this->logger->info("listener response.  ", [$response]);
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->matcher instanceof Router) {
            $parameters = $this->matcher->matchRequest($request);
        } else {
            $parameters = $this->matcher->match($request->getPathInfo());
        }

        if (null !== $this->logger) {
            $this->logger->info('listener request Matched route "{route}".', array(
                'route' => isset($parameters['_route']) ? $parameters['_route'] : 'n/a',
                'route_parameters' => $parameters,
                'request_uri' => $request->getUri(),
                'method' => $request->getMethod(),
            ));
        }
    }
}