<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;
use Twig_Environment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
/**
 * Description of Fotorama
 *
 * @author Trey
 */
class Fotorama {
    private $twig;
    private $eventDispatcher;
    
    private $id;
    private $files;
    
    //fotorama settings
    public $width = '100%';
    public $minWidth = '50%';
    public $maxWidth = '100%';
    public $height = '50%';
    public $minHeight = '25%';
    public $maxHeight = '75%';
    public $ratio = '800/600';
    public $margin = 5;
    public $glimpse = 0;
    public $nav = 'thumbs';
    public $navPosition = 'bottom';
    public $navWidth = '100%';
    public $thumbWidth = 75;
    public $thumbHeight = 75;
    public $thumbMargin = 1;
    public $thumbBorderWidth = 1;
    public $allowFullScreen = 'true';
    public $fit = 'scaledown';
    public $thumbFit = 'scaledown';
    public $transition = 'slide';
    public $clickTransition = 'slide';
    public $transitionDuration = 750;
    public $captions = 'true';
    public $hash = 'false';
    public $startIndex = 0;
    public $loop = 'true';
    public $autoPlay = 'true';
    public $stopAutoplayOnTouch = 'true';
    public $keyboard = 'true';
    public $arrows = 'always';
    public $click = 'true';
    public $swipe = 'true';
    public $trackPad = 'true';
    public $shuffle = 'false';
    public $direction = 'ltr';
    public $shadows = 'true';
    
    public function __construct(Twig_Environment $twig, EventDispatcherInterface $eventDispatcher) 
    {
        $this->twig = $twig;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId(string $id)
    {
        $this->id = $id;
    }
    
    public function setHeight(string $height)
    {
        $this->height = $height;
    }
    
    public function initFotorama()
    {
        $javascriptContent = $this->twig->render('libraries/fotorama/main.js.html.twig', array(
            'id'=>$this->id,
            'width'=>$this->width,
            'minwidth'=>$this->minWidth,
            'maxwidth'=>$this->maxWidth,
            'height'=>$this->height,
            'minheight'=>$this->minHeight,
            'maxheight'=>$this->maxHeight,
            'ratio'=>$this->ratio,
            'margin'=>$this->margin,
            'glimpse'=>$this->glimpse,
            'nav'=>$this->nav,
            'navposition'=>$this->navPosition,
            'navwidth'=>$this->navWidth,
            'thumbwidth'=>$this->thumbWidth,
            'thumbheight'=>$this->thumbHeight,
            'thumbmargin'=>$this->thumbMargin,
            'thumbborderwidth'=>$this->thumbBorderWidth,
            'allowfullscreen'=>$this->allowFullScreen,
            'fit'=>$this->fit,
            'thumbfit'=>$this->thumbFit,
            'transition'=>$this->transition,
            'clicktransition'=>$this->clickTransition,
            'transitionduration'=>$this->transitionDuration,
            'captions'=>$this->captions,
            'hash'=>$this->hash,
            'startindex'=>$this->startIndex,
            'loop'=>$this->loop,
            'autoplay'=>$this->autoPlay,
            'stopautoplayontouch'=>$this->stopAutoplayOnTouch,
            'keyboard'=>$this->keyboard,
            'arrows'=>$this->arrows,
            'click'=>$this->click,
            'swipe'=>$this->swipe,
            'trackpad'=>$this->trackPad,
            'shuffle'=>$this->shuffle,
            'direction'=>$this->direction,
            'shadows'=>$this->shadows,
        ));
        $cssContent = $this->twig->render('libraries/fotorama/main.css.html.twig',array());
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
    public function addFiles($files)
    {
        $this->files = $files;
    }
    public function createView()
    {
        $this->initFotorama();
        $slideshow = $this->twig->render('libraries/fotorama/slideshow.html.twig',array('files'=>$this->files, 'id'=>$this->id));
        return $slideshow;
    }
}