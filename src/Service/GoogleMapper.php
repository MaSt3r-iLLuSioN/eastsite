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
 * Description of GoogleMapper
 *
 * @author Trey
 */
class GoogleMapper {
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
        $this->latLong = $this->buildLatAndLong();
    }
    private function buildLatAndLong()
    {
        $latLong = array();
        if(is_array($this->node))
        {
            $temp = array();
            foreach($this->node as $node)
            {
                $ll = $this->queryMaps($node->getLocation());
                $temp['title'] = $node->getTitle();
                $temp['lat'] = $ll['lat'];
                $temp['long'] = $ll['long'];
                $latLong[] = $temp;
            }
        }
        elseif(is_object($this->node))
        {
            $ll = $this->queryMaps($this->node->getLocation());
            $latLong['single'] = true;
            $latLong['title'] = $this->node->getTitle();
            $latLong['lat'] = $ll['lat'];
            $latLong['long'] = $ll['long'];
        }
        return $latLong;
    }
    private function queryMaps(string $location)
    {
        #Find latitude and longitude
        $ll = array();
        $url = "https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyDPbWDH6GztUwZ9472TOsKMSHvR9rmIOnc&address=$location";
        $json_data = file_get_contents($url);
        $result = json_decode($json_data, TRUE);
        $ll['lat'] = $result['results'][0]['geometry']['location']['lat'];
        $ll['long'] = $result['results'][0]['geometry']['location']['lng'];
        return $ll;
    }
    public function initMapper()
    {
        $javascriptContent = $this->twig->render('libraries/googlemapper/main.js.html.twig', array(
            'latLong'=>$this->latLong
        ));
        $cssContent = $this->twig->render('libraries/googlemapper/main.css.html.twig',array());
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
        $this->initMapper();
        $mapperTemplate = $this->twig->render('libraries/googlemapper/mapper.html.twig', array(
            'node'=>$this->node,
            'uid'=>$this->uid
        ));
        return $mapperTemplate;
    }
    
}
