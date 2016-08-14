<?php
/**
 * Created by PhpStorm.
 * User: LatteCake
 * Date: 16/7/23
 * Time: 下午3:26
 * File: AuthenticationSuccessHandler.php
 */

namespace AppBundle\Handler;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AuthenticationSuccessHandler
 * @package AppBundle\Handler
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            $url = $this->determineTargetUrl($request);
            if (!preg_match('/http/', $url)) {
                $url = $request->getBaseUrl() . $url;
            }

            $data = array(
                'success' => true,
                'url' => $url,
            );
            $response = new JsonResponse($data, 200, ['Access-Control-Allow-Origin' => '*']);
            return $response;
        } else {
            return parent::onAuthenticationSuccess($request, $token);
        }
    }

    /**
     * Builds the target URL according to the defined options.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function determineTargetUrl(Request $request)
    {
        if ($this->options['always_use_default_target_path']) {
            return $this->options['default_target_path'];
        }
        if ($targetUrl = $request->get($this->options['target_path_parameter'], null, true)) {
            return $targetUrl;
        }
        if (null !== $this->providerKey && $targetUrl = $request->getSession()->get('_security.' . $this->providerKey . '.target_path')) {
            $request->getSession()->remove('_security.' . $this->providerKey . '.target_path');
            return $targetUrl;
        }
        if ($this->options['use_referer'] && ($targetUrl = $request->headers->get('Referer')) && $targetUrl !== $this->httpUtils->generateUri($request, $this->options['login_path'])) {
            return $targetUrl;
        }
        return $this->options['default_target_path'];
    }

    /**
     * Establece el traductor
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}