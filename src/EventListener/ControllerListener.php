<?php
namespace App\EventListener;

use App\Entity\Config;
use App\Entity\PageEntity;
use App\Entity\LayoutEntity;
use App\Entity\RegionEntity;
use App\Service\Breadcrumbs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\ORM\EntityManagerInterface;
use Twig_Environment;
use Symfony\Component\HttpFoundation\RequestStack;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ControllerListener
 *
 * @author M@St3r_iLLuSioN
 */
class ControllerListener implements EventSubscriberInterface {
    //put your code here
    private $em;
    private $twig;
    private $container;
    private $request;
    private $dispatcher;
    
    public function __construct(EntityManagerInterface $em, Twig_Environment $env, RequestStack $requestStack, ContainerInterface $container)
    {
        $this->em = $em;
        $this->twig = $env;
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $container;
        $this->dispatcher = $container->get('event_dispatcher');
        
    }
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }
        
        $routeName = $event->getRequest()->get('_route');
        //var_dump($routeName);
        //exit();
        $headerBlocks = null;
        $contentTopBlocks = null;
        $contentBlocks = null;
        $contentBottomBlocks = null;
        $footerBlocks = null;
        $sidebarLeft = false;
        $sidebarLeftBlocks = null;
        $sidebarRight = false;
        $sidebarRightBlocks = null;
        $sidebarBoth = false;
        $contentSize = '12';
        if($routeName == 'page')
        {
            $page = $this->em->getRepository(PageEntity::class)->findBy(array('url'=>$event->getRequest()->get('url')));
            //get page layout
            $regions = $page[0]->getLayout()->getRegions();
        }
        else
        {
            //default to both sidebars id:7
            $regions = $this->em->getRepository(LayoutEntity::class)->find(7)->getRegions();
        }
        //itterate through all the blocks and set there classes (whos types are set of course!)
        $blocks = $this->em->getRepository(\App\Entity\BlockEntity::class)->findBy(array('typeset'=>1));
        foreach($blocks as $block)
        {
            if($block->getEnabled() == true)
                $block->setBlockClass($this->em, $this->container);
        }
        foreach($regions as $region)
        {
            if($region->getTitle() == 'Sidebar Left')
            {
                
                $sidebarLeftBlocks = $this->setRegionBlocksForDisplay($region->getBlocks());
                $active = false;
                foreach($sidebarLeftBlocks as $block)
                {
                    if($block->getId() > 0 && $block->getEnabled() == true)
                        $active = true;
                }
                if($active)
                {
                    $contentSize = '9';
                    $sidebarLeft = true;
                }
            }
            elseif($region->getTitle() == 'Sidebar Right')
            {
                $sidebarRightBlocks = $this->setRegionBlocksForDisplay($region->getBlocks());
                $active = false;
                foreach($sidebarRightBlocks as $block)
                {
                    if($block->getId() > 0 && $block->getEnabled() == true)
                        $active = true;
                }
                if($active)
                {
                    $contentSize = '9';
                    $sidebarRight = true;
                }
            }
            elseif($region->getTitle() == 'Sidebar Both')
            {
                $sidebarLeft = false;
                $sidebarRight = false;
                foreach($regions as $r)
                {
                    if($r->getTitle() == 'Sidebar Left')
                    {
                        $sidebarLeftBlocks = $this->setRegionBlocksForDisplay($r->getBlocks());
                        $active = false;
                        foreach($sidebarLeftBlocks as $block)
                        {
                            if($block->getId() > 0 && $block->getEnabled() == true)
                                $active = true;
                        }
                        if($active)
                        {
                            $contentSize = '9';
                            $sidebarLeft = true;
                        }
                        break;
                    }
                }
                foreach($regions as $r)
                {
                    if($r->getTitle() == 'Sidebar Right')
                    {
                        $sidebarRightBlocks = $this->setRegionBlocksForDisplay($r->getBlocks());
                        $active = false;
                        foreach($sidebarRightBlocks as $block)
                        {
                            if($block->getId() > 0 && $block->getEnabled() == true)
                                $active = true;
                        }
                        if($active)
                        {
                            $contentSize = '9';
                            $sidebarRight = true;
                        }
                        break;
                    }
                }
                if($sidebarLeft == true && $sidebarRight == true)
                {
                    $sidebarBoth = true;
                    $contentSize = '6';
                }
            }
            elseif($region->getTitle() == 'Content Top') 
            {
                $contentTopBlocks = $this->setRegionBlocksForDisplay($region->getBlocks());
            }
            elseif($region->getTitle() == 'Content')
            {
                $contentBlocks = $this->setRegionBlocksForDisplay($region->getBlocks());
            }
            elseif($region->getTitle() == 'Content Bottom')
            {
                $contentBottomBlocks = $this->setRegionBlocksForDisplay($region->getBlocks());
            }
            elseif($region->getTitle() == 'Footer')
            {
                
                $footerBlocks = $this->setRegionBlocksForDisplay($region->getBlocks());
            }
        }
        
        $config = $this->em->getRepository(Config::class)
        ->find(1);

        $siteMenu = $this->em->getRepository(\App\Entity\MenuEntity::class)->find(7);
        $siteMenuItems = array();
        $siteMI = $siteMenu->getItems();
        foreach($siteMI as $item)
        {
            if($item->getParent() == null)
                $siteMenuItems[$item->getTitle()] = $item;
        }
        $adminMenu = $this->em->getRepository(\App\Entity\MenuEntity::class)->find(6);
        $adminMenuItems = array();
        $adminMI = $adminMenu->getItems();
        foreach($adminMI as $item)
        {
            if($item->getParent() == null)
                $adminMenuItems[] = $item;
        }
        // Twig global
        $this->twig->addGlobal('CurrentURI', $_SERVER['REQUEST_URI']);
        $this->twig->addGlobal('config',$config);
        $this->twig->addGlobal('siteMenu',$siteMenuItems);
        $this->twig->addGlobal('adminMenu', $adminMenuItems);
        //adding blocks as global        
        $this->twig->addGlobal('header_blocks', $headerBlocks);
        $this->twig->addGlobal('content_top_blocks', $contentTopBlocks);
        $this->twig->addGlobal('content_blocks',$contentBlocks);
        $this->twig->addGlobal('content_bottom_blocks',$contentBottomBlocks);
        $this->twig->addGlobal('footer_blocks',$footerBlocks);
        $this->twig->addGlobal('left_sidebar_blocks',$sidebarLeftBlocks);
        $this->twig->addGlobal('right_sidebar_blocks',$sidebarRightBlocks);
        $this->twig->addGlobal('left_sidebar_on', $sidebarLeft);
        $this->twig->addGlobal('right_sidebar_on',$sidebarRight);
        $this->twig->addGlobal('both_sidebars_on',$sidebarBoth);
        if($sidebarLeft && $sidebarRight)
        {
            $contentSize = '6';
        }
        $this->twig->addGlobal('layout_content_size', $contentSize);
        
        
    }
    
    private function setRegionBlocksForDisplay($blocks)
    {
        $returnBlocks = array();
        $urlArgs = explode('/',$this->request->getPathInfo());
        $urlArgCount = count($urlArgs);
        foreach($blocks as $block)
        {
            $blockListedPages = explode(',',$block->getListedpages());
            if($block->getOnpage() == true)
            {
                //check each listed page against the url
                foreach($blockListedPages as $listedPage)
                {
                    $listedArgs = explode('/',$listedPage);
                    $listedArgsCount = count($listedArgs);
                    //var_dump($listedArgsCount);
                    //exit();
                    //fist check to see if this block will display on all urls after url- * is the wildcard ex: /blog/* displays block on blog page and all pages containing blog in url
                    
                    if($listedArgsCount > 2 &&  $urlArgCount > 1 && $urlArgs[1] == $listedArgs[1] && $listedArgs[2] == '*')
                    {
                        $returnBlocks[] = $block;
                        break;
                    }
                    //all pages or homepage
                    elseif ($listedArgsCount > 1 && $listedArgs[1] == '*' || $listedArgsCount > 1 && $listedArgs[1] == 'home' && $urlArgs[1] == '') 
                    {
                        $returnBlocks[] = $block;
                        break;
                    }
                    elseif($listedArgs[$listedArgsCount-1] == '*' && $urlArgCount >= $listedArgsCount-1)
                    {
                        //make sure the other listedargs match the url
                        $count = $listedArgsCount - 2;
                        if($count <= $urlArgCount && $count >= 0)
                        {
                            $same = true;
                            for($i = 0; $i <= $count; $i++)
                            {
                                if($listedArgs[$i] != $urlArgs[$i])
                                {
                                    $same = false;
                                    break;
                                }
                            }
                            if($same == true)
                            {
                                $returnBlocks[] = $block;
                                break;
                            }
                        }
                        else
                        {
                            //not the same
                        }
                    }
                    //check to see if listed page and url have same ammount of arguments
                    elseif($urlArgCount == $listedArgsCount)
                    {
                        $same = true;
                        //they have the same ammount of args -make sure they all match!
                        foreach($urlArgs as $key=>$value)
                        {
                            if($key == 0)
                                continue;
                            if($urlArgs[$key] != $listedArgs[$key])
                            {
                                $same = false;
                                break;
                            }
                        }
                        if($same == true)
                        {
                            $returnBlocks[] = $block;
                            break;
                        }
                    }
                }
            }
            else
            {
                $found = false;
                //display the block if this page isnt on the list
                //check each listed page against the url
                foreach($blockListedPages as $listedPage)
                {
                    $listedArgs = explode('/',$listedPage);
                    $listedArgsCount = count($listedArgs);
                    
                    if($listedArgsCount > 2 &&  $urlArgCount > 1 && $urlArgs[1] == $listedArgs[1] && $listedArgs[2] == '*')
                    {
                        $found = true;
                    }
                    elseif ($listedArgsCount > 1 && $listedArgs[1] == '*' || $listedArgsCount > 1 && $listedArgs[1] == 'home' && $urlArgs[1] == '') 
                    {
                        $found = true;
                    }
                    elseif($listedArgs[$listedArgsCount-1] == '*' && $urlArgCount >= $listedArgsCount-1)
                    {
                        //make sure the other listedargs match the url
                        $count = $listedArgsCount - 2;
                        if($count <= $urlArgCount && $count >= 0)
                        {
                            $same = true;
                            for($i = 0; $i <= $count; $i++)
                            {
                                if($listedArgs[$i] != $urlArgs[$i])
                                {
                                    $same = false;
                                    break;
                                }
                            }
                            if($same == true)
                            {
                                $found = true;
                            }
                        }
                        else
                        {
                            //not the same
                        }
                    }
                    elseif($urlArgCount == $listedArgsCount)
                    {
                        $same = true;
                        //they have the same ammount of args -make sure they all match!
                        foreach($urlArgs as $key=>$value)
                        {
                            if($key == 0)
                                continue;
                            if($urlArgs[$key] != $listedArgs[$key])
                            {
                                $same = false;
                                break;
                            }
                            
                        }
                        if($same == true)
                        {
                            $found = true;
                        }
                    }
                }
                if($found == false)
                {
                    $returnBlocks[] = $block;
                }
            }
        }
        return $returnBlocks;
    }
    
    public static function getSubscribedEvents(): array {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

}
