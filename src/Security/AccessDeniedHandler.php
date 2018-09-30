<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Description of AccessDeniedHandler
 *
 * @author Trey
 */
class AccessDeniedHandler implements AccessDeniedHandlerInterface, LogoutHandlerInterface
{
    //put your code here
    private $container;
    
    public function __construct(ContainerInterface $container) 
    {
        $this->container = $container;
    }
    
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $this->container->get('session')->getFlashBag()->add('warning', 'You do not have the correct permissions to view that page!');
        return $this->redirectToRoute ('account');
    }
    
    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }
    
    protected function redirectToRoute(string $route, array $parameters = array(), int $status = 302): RedirectResponse
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }
    
    protected function generateUrl(string $route, array $parameters = array(), int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    public function logout(Request $request, Response $response, TokenInterface $token) 
    {
        
    }

}
