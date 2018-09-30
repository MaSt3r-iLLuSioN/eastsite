<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;
/**
 * Description of TwigFunctions
 *
 * @author Trey
 */
class TwigFunctions extends \Twig\Extension\AbstractExtension {
    //put your code here
    private $container;
    private $twig;
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->twig = $this->container->get('twig');
    }
    public function getFunctions() {
        return array(
            new \Twig_Function('setGlobally',array($this,'setGlobally')),
            new \Twig_Function('getGlobally', array($this, 'getGlobally')),
        );
    }
    public function setGlobally($name,$value)
    {
        $this->twig->addGlobal($name, $value);
    }
    public function getGlobally($name)
    {
        return $this->twig->getGlobal($name);
    }
}
