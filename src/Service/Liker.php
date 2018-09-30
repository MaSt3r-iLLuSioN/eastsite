<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use Twig_Environment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of Liker
 *
 * @author Trey
 */
class Liker {
    private $twig;
    private $eventDispatcher;
    private $em;
    private $authChecker;
    private $user;
    
    private $node;
    private $uid;
    
    public function __construct(Twig_Environment $twig, EventDispatcherInterface $eventDispatcher, EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage) 
    {
        $this->twig = $twig;
        $this->eventDispatcher = $eventDispatcher;
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->user = $tokenStorage->getToken()->getUser();
        
        if($authChecker->isGranted('ROLE_USER'))
            $this->uid = $this->user->getId();
        else
            $this->uid = 0;
    }
    
    public function setNode($node)
    {
        $this->node = $node;
    }
    
    private function initLiker()
    {
        $javascriptContent = $this->twig->render('libraries/liker/main.js.html.twig', array(
            'node'=>$this->node,
            'uid'=>$this->uid
        ));
        $cssContent = $this->twig->render('libraries/liker/main.css.html.twig',array());
        $this->eventDispatcher->addListener('kernel.response', function($event) use ($javascriptContent, $cssContent) {
            $response = $event->getResponse();
            $content = $response->getContent();
            // finding position of </body> tag to add content before the end of the tag
            $pos = strripos($content, '</body>');
            $content = substr($content, 0, $pos).$javascriptContent.substr($content, $pos);

            $pos = strripos($content, '</head>');
            $content = substr($content, 0, $pos).$cssContent.substr($content, $pos);
            
            $response->setContent($content);
            $event->setResponse($response);
        });
    }
    public function createView()
    {
        $this->initLiker();
        $likeTemplate = $this->twig->render('libraries/liker/liker.html.twig', array(
            'node'=>$this->node,
            'uid'=>$this->uid
        ));
        return $likeTemplate;
    }
}
