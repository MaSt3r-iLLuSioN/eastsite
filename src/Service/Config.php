<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
/**
 * Description of Config
 *
 * @author Trey
 */
class Config {
    //put your code here
    private $em;
    private $config;
    private $container;
    
    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        $this->container = $container;
        $this->em = $em;
        $this->setConfig();
    }
    
    private function setConfig()
    {
        $configs = $this->em->getRepository(\App\Entity\Config::class)->findAll();
        foreach($configs as $c)
        {
            //the first one is always the site config
            $this->config = $c;
            break;
        }
    }
    
    public function getConfig()
    {
        return $this->config;
    }
    
    public function getSiteUrl()
    {
        return $this->config->getSiteurl();
    }
    
    public function getGoogleAccount(string $redirectUrl, Request $request, array $scopes)
    {
        return $this->config->getGoogleuser()->getGoogleClient($this->container, $redirectUrl,$request,$scopes);
    }
}
