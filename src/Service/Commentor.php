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
 * Description of Commentor
 *
 * @author Trey
 */
class Commentor {
    private $twig;
    private $eventDispatcher;
    private $em;
    private $authChecker;
    private $user;
    
    private $unregisteredUsers;
    
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
    
    public function setUnregisteredUsers($bool)
    {
        $this->unregisteredUsers = $bool;
    }
    
    public function setNode($node)
    {
        $this->node = $node;
    }
    
    public function createView()
    {
        $commentTemplate = $this->twig->render('libraries/commentor/commentor.html.twig', array(
            'node'=>$this->node,
            'uid'=>$this->uid,
            'unregisteredUsers'=>$this->unregisteredUsers
        ));
        return $commentTemplate;
    }
}
