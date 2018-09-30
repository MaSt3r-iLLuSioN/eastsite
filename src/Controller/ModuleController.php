<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;
use App\Service\Breadcrumbs;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
/**
 * Description of ModuleController
 *
 * @author Trey
 */
class ModuleController {
    /**
     * @Route("/admin/modules", name="moduleList")
     */
    //main page that list modules in backend
    public function moduleList(Breadcrumbs $breadcrumbs)
    {
        
    }
    /**
     * @Route("/admin/modules/enable/{moduleName}", name="enableModule")
     */
    //enables said module and uses previous module data if there
    public function enableModule(string $moduleName, Breadcrumbs $breadcrumbs)
    {
        
    }
    /**
     * @Route("/admin/modules/disable/{moduleName}", name="disableModule")
     */
    //disables said module but keeps all data from module
    public function disableModule(string $moduleName, Breadcrumbs $breadcrumbs)
    {
        
    }
    /**
     * @Route("/admin/modules/install/{moduleName}", name="installModule")
     */
    //installs module
    public function installModule(string $moduleName, Breadcrumbs $breadcrumbs)
    {
        
    }
    /**
     * @Route("/admin/modules/uninstall/{moduleName}", name="viewMenus")
     */
    //uninstalls said module and deletes all data (depending on if implemented by module)
    public function uninstallModule(string $moduleName, Breadcrumbs $breadcrumbs)
    {
        
    }
}