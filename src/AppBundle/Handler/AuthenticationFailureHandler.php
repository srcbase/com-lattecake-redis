<?php
/**
 * Created by PhpStorm.
 * User: LatteCake
 * Date: 16/7/23
 * Time: 下午3:23
 * File: AuthenticationFailureHandler.php
 */

namespace AppBundle\Handler;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Psr\Log\LoggerInterface;

/**
 * Class AuthenticationFailureHandler
 * @package AppBundle\Handler
 */
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $token;


    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options = array(), LoggerInterface $logger = null, UriSafeTokenGenerator $token)
    {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
        $this->token = $token;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $message = $exception->getMessageKey();
            $messageTrans = $this->translator->trans($message, array(), 'FOSUserBundle');
            if ($messageTrans === $message) {
                $messageTrans = $this->translator->trans($message, array(), 'security');
            }
            $data = array(
                'success' => false,
                'message' => $messageTrans
            );
            $response = new JsonResponse($data, 400, ['Access-Control-Allow-Origin' => '*']);
            return $response;
        } else {
            return parent::onAuthenticationFailure($request, $exception);
        }
    }

    /**
     * Establece el traductor
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    function setTranslator(TranslatorInterface $translator) {
        $this->translator = $translator;
    }
}