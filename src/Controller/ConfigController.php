<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Breadcrumbs;

class ConfigController extends BaseController
{
    /*
     * @Route("/admin/calendar/config", name="calendarConfig")
     */
    public function calendarConfig(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Calendar Configuration');
        $breadcrumbs->setBreadcrumbs();
        
        return $this->render('config/calendar-config.html.twig');
    }
    /*
     * @Route("/admin/users/config", name="userConfig")
     */
    public function userConfig(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('User Configuration');
        $breadcrumbs->setBreadcrumbs();
        
        return $this->render('config/user-config.html.twig');
    }
    /*
     * @Route("/admin/pages/config", name="pagesConfig")
     */
    public function pagesConfig(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Pages Configuration');
        $breadcrumbs->setBreadcrumbs();
        
        return $this->render('config/pages-config.html.twig');
    }
    /*
     * @Route("/admin/blog/config", name="blogConfig")
     */
    public function blogConfig(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Blog Configuration');
        $breadcrumbs->setBreadcrumbs();
        
        return $this->render('config/blog-config.html.twig');
    }
    /**
     * @Route("/admin/config", name="siteConfig")
     */
    public function siteConfig(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Site Configuration');
        $breadcrumbs->setBreadcrumbs();
        // replace this line with your own code!
        return $this->render('config/site-config.html.twig');
    }
    /*
     * @Route("/admin", name="adminDashboard")
     */
    public function adminDashboard(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->setActive('Admin Dashboard');
        $breadcrumbs->setBreadcrumbs();
        
        return $this->render('config/admin-dashboard.html.twig');
    }
}
