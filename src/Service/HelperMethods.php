<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\MenuItemEntity;
use Twig_Environment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;
/**
 * Description of HelperMethods
 *
 * @author Trey
 */
class HelperMethods {
    private $twig;
    private $eventDispatcher;
    private $em;
    
    public function __construct(Twig_Environment $twig, EventDispatcherInterface $eventDispatcher, EntityManagerInterface $em) 
    {
        $this->twig = $twig;
        $this->eventDispatcher = $eventDispatcher;
        $this->em = $em;
    }
    public function buildRecursiveMenuArray(MenuItemEntity $menu, array $menuArray, int $iterationNum, int $activeMenuId = 0)
    {
        
        $startName = '';
        for($i = 1; $i <= $iterationNum; $i++)
        {
            $startName = $startName . '-';
        }
        if($activeMenuId != $menu->getId() || $activeMenuId == 0)
        {
            $menuArray[$startName . $menu->getTitle()] = $menu->getId();
            if($menu->getChildrencount() > 0)
            {
                foreach($menu->getChildren() as $child)
                {
                    $menuArray = $this->buildRecursiveMenuArray($child, $menuArray, $iterationNum + 1, $activeMenuId);
                }
            }
        }
        return $menuArray;
    }
}
