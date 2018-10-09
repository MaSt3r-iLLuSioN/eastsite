<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\EventListener;
use App\Service\Breadcrumbs;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Profiler\Profiler;
/**
 * Description of ExceptionListener
 *
 * @author Trey
 */
class ExceptionListener {
    private $breadcrumbs;
    protected $profiler = null;        
    public function __construct(Breadcrumbs $breadcrumbs,Profiler $profiler)
    {
        $this->breadcrumbs = $breadcrumbs;
        if($profiler != null)
            $this->profiler = $profiler;
    }
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $this->breadcrumbs->addBreadcrumb('Home', '/');
        $exception = $event->getException();
        
        if($exception instanceof NotFoundHttpException)
        {
            //this is a 404 error
            $this->breadcrumbs->setActive('Page Not Found (404)');
            $this->profiler->disable();
        }
        elseif($exception instanceof \Symfony\Component\Process\Exception\LogicException)
        {
            $this->profiler->disable();
        }
        elseif($exception instanceof \Twig_Error_Runtime)
        {
            $this->profiler->disable();
        }
        
        $this->breadcrumbs->setBreadcrumbs();
        
    }
}
