<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Description of BaseController
 *
 * @author Trey
 */
class BaseController extends Controller {
    //put your code here
    protected $profiler = null;
    
    public function __construct(Profiler $profiler) {
        if($profiler != null)
            $this->profiler = $profiler;
    }
    public function hideProfiler($user)
    {
        if ($this->profiler == null)
            return;
        $em = $this->getDoctrine()->getManager();
        $role = $em->getRepository(\App\Entity\RoleEntity::class)->find(1);
        
        if(is_array($user) || is_object($user))
        {
            if(!$user->hasRole($role))
            {
                $this->profiler->disable();
            }
        }
        else
            $this->profiler->disable();
    }
}
