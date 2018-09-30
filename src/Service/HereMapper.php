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
 * Description of HereMapper
 *
 * @author Trey
 */
class HereMapper {
    //put your code here
    private $twig;
    private $eventDispatcher;
    private $em;
    private $authChecker;
    private $user;
    
    private $node;
    private $latLong;
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
    public function getGeoCords(string $location)
    {
        $cords = array();
        $url = 'https://geocoder.api.here.com/6.2/geocode.json?';
        $options = array(
            'app_id'=>'LGcmCQ76osKVojBa2DkY',
            'app_code'=>'CBIweNpu96F1PDB9Ox0sRA',
            'searchtext'=>$location);
        $url .= http_build_query($options,'','&');
        
        $json_data = file_get_contents($url);
        $result = json_decode($json_data, TRUE);
        $cords['lat'] = $result['Response']['View'][0]['Result'][0]['Location']['DisplayPosition']['Latitude'];
        $cords['long'] = $result['Response']['View'][0]['Result'][0]['Location']['DisplayPosition']['Longitude'];
        return $cords;
    }
    public function initMapper()
    {
        $headjsContent = $this->twig->render('libraries/heremapper/head.js.html.twig', array(
        ));
        $javascriptContent = $this->twig->render('libraries/heremapper/main.js.html.twig', array(
            'nodes'=>$this->node
        ));
        $cssContent = $this->twig->render('libraries/heremapper/main.css.html.twig',array());
        $this->eventDispatcher->addListener('kernel.response', function($event) use ($javascriptContent, $cssContent,$headjsContent) {
            $response = $event->getResponse();
            $content = $response->getContent();
            
            $pos = strripos($content, '</head>');
            $content = substr($content, 0, $pos).$headjsContent.substr($content, $pos);
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
        $this->initMapper();
        $mapperTemplate = $this->twig->render('libraries/heremapper/mapper.html.twig', array(
            'node'=>$this->node,
            'uid'=>$this->uid
        ));
        return $mapperTemplate;
    }
}
