<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return [

        ];
    }

//    /**
//     *
//     * @Route("/app.json")
//     *
//     * @return Response
//     */
//    public function jsonAction()
//    {
//        return new Response(file_get_contents('./app.json'));
//    }

    /**
     *
     * @Route("/getToken")
     *
     * @return JsonResponse
     */
    public function getTokenAction()
    {
        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

        return new JsonResponse([
            'csrf' => $csrfToken
        ], 200, ['Access-Control-Allow-Origin' => '*']);
    }
}
