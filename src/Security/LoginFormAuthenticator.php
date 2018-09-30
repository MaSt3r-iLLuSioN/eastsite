<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Security;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
/**
 * Description of LoginFormAuthenticator
 *
 * @author M@St3r_iLLuSioN
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator{
    //put your code here
    private $router;
    private $encoder;
    private $failMessage;
    private $csrf;
    public function __construct(RouterInterface $router, UserPasswordEncoderInterface $passwordEncoder, CsrfTokenManagerInterface $csrf)
    {
        $this->router = $router;
        $this->failMessage = 'Invalid Credentials';
        $this->encoder = $passwordEncoder;
        $this->csrf = $csrf;
    }
    protected function getLoginUrl(): string {
        return $this->router->generate('login');
    }

    public function checkCredentials($credentials, UserInterface $user): bool {
        if($this->encoder->isPasswordValid($user, $credentials['password']))
        {    
            return true;
        }
        else
        {
            $this->failMessage = 'Incorrect Password!';
            throw new CustomUserMessageAuthenticationException($this->failMessage);
        }
    }

    public function getCredentials(Request $request) {
        if($request->getPathInfo() != '/login' || !$request->isMethod('POST'))
            return;
        $form = $request->request->get('form');
        $csrfToken = $request->request->get('_csrf_token');
        if (false === $this->csrf->isTokenValid(new CsrfToken('authenticate', $csrfToken))) {
            throw new InvalidCsrfTokenException('Invalid CSRF token.');
        }
        return array(
            'username' => $form['_username'],
            'password' => $form['_password']
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        try{
            return $userProvider->loadUserByUsername($credentials['username']);
        } catch (Exception $ex) {
            $this->failMessage = 'Username or Email not found!';
            throw new CustomUserMessageAuthenticationException($this->failMessage);
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        // if the user hits a secure page and start() was called, this was
        // the URL they were on, and probably where you want to redirect to
        $url = $this->router->generate('account');
        return new RedirectResponse($url);
    }

    public function supports(Request $request): bool {
        $isLoginSubmit = $request->getPathInfo() == '/login' && $request->isMethod('POST');
        if(!$isLoginSubmit)
            return false;
        else
            return true;
    }

}
