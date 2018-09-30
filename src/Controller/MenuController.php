<?php

namespace App\Controller;
use App\Entity\MenuEntity;
use App\Entity\MenuItemEntity;
use App\Entity\RoleEntity;
use App\Entity\CategoryGroup;
use App\Service\Breadcrumbs;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MenuController extends BaseController
{
    /**
     * @Route("/admin/menus", name="viewMenus")
     */
    public function viewMenus(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('admin_view_menus', null);
        
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Menus');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        //get all menus
        $menus = $em->getRepository(MenuEntity::class)->findAll();
        
        return $this->render('menus/view-menus.html.twig', array(
            'menus'=>$menus,
        ));
    }
    
    /**
     * @Route("/admin/menus/add", name="addMenu")
     */
    public function addMenu(Request $request, Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('add_menu', null);
        
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Menus', '/admin/menus');
        $breadcrumbs->setActive('Add Menu');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository(RoleEntity::class)->findAll();
        
        $typeChoices = array();
        $typeChoices['All'] = 0;
        foreach($roles as $role)
        {
            $typeChoices[$role->getTitle()] = $role->getId();
        }
        
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();
        $form = $formFactory->createBuilder()
            ->add('title', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Menu Title',
                    'class' => 'form-control',
                    'name' => 'title'
                )
            ))
            ->add('role', ChoiceType::class, array(
                'choices'=> $typeChoices,
                'attr' => array(
                    'class' => 'form-control',
                    'name' => 'role'
                )
            ))
            
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $menu = new MenuEntity();
            $menu->setTitle($form->get('title')->getData());
            $roleForm = $form->get('role')->getData();
            if($roleForm != 0)
            {
                $role = $em->getRepository(RoleEntity::class)->find($roleForm);
                $menu->setRole($role);
            }
            $em->persist($menu);
            $em->flush();
            
            $this->addFlash('notice', 'Menu ' . $menu->getTitle() . ' was made successfully, add some items to it!');
            return $this->redirectToRoute('addMenuItem', array('menu'=>$menu->getId()));
        }
        
        return $this->render('menus/add-menu.html.twig', array(
            'form'=>$form->createView(),
        ));
    }
    
    /**
     * @Route("/admin/menus/{menu}/edit", name="editMenu")
     */
    public function editMenu(MenuEntity $menu, Request $request, Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('edit_menu', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Menus', '/admin/menus');
        $breadcrumbs->addBreadcrumb($menu->getTitle(), '/admin/menus/'.$menu->getId());
        $breadcrumbs->setActive('Edit Menu');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository(RoleEntity::class)->findAll();
        
        $typeChoices = array();
        $typeChoices['All'] = 0;
        foreach($roles as $role)
        {
            $typeChoices[$role->getTitle()] = $role->getId();
        }
        $roleData = 0;
        
        if($menu->getRole() != null)
            $roleData = $menu->getRole ()->getId();
        
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();
        $form = $formFactory->createBuilder()
            ->add('title', TextType::class, array(
                'data'=>$menu->getTitle(),
                'attr' => array(
                    'placeholder' => 'Menu Title',
                    'class' => 'form-control',
                    'name' => 'title'
                )
            ))
            ->add('role', ChoiceType::class, array(
                'data'=>$roleData,
                'choices'=> $typeChoices,
                'attr' => array(
                    'class' => 'form-control',
                    'name' => 'role'
                )
            ))
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $menu->setTitle($form->get('title')->getData());
            $roleForm = $form->get('role')->getData();
            if($roleForm != 0)
            {
                $role = $em->getRepository(RoleEntity::class)->find($roleForm);
                $menu->setRole($role);
            }
            else
            {
                $menu->setRole(null);
            }
            $em->persist($menu);
            $em->flush();
            
            $this->addFlash('notice', 'Menu ' . $menu->getTitle() . ' was edited successfully!');
            return $this->redirectToRoute('viewMenu', array('menu'=>$menu->getId()));
        }
        
        return $this->render('menus/edit-menu.html.twig', array(
            'form'=>$form->createView(),
            'menu'=>$menu
        ));
    }
    
    /**
     * @Route("/admin/menus/{menu}/delete", name="deleteMenu")
     */
    public function deleteMenu(MenuEntity $menu, Request $request, Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('delete_menu', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Menus', '/admin/menus');
        $breadcrumbs->addBreadcrumb($menu->getTitle(), '/admin/menus/'.$menu->getId());
        $breadcrumbs->setActive('Delete Menu');
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('hidden', HiddenType::class, array(
                    
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            //get categories of group and remove them all
            foreach($menu->getItems() as $item)
            {
                if($item->getParent() == null)
                    $this->recursiveDeleteMenuItem ($item, $em);
            }
            $em->remove($menu);
            $em->flush();
            
            $this->addFlash('notice', 'Menu: '.$menu->getTitle() . ' was removed successfully!');
            return $this->redirectToRoute('viewMenus');
        }
        return $this->render('menus/delete-menu.html.twig', array(
            'menu' => $menu,
            'form' => $form->createView()
        ));
    }
    private function recursiveDeleteMenuItem(MenuItemEntity $menuItem, $em)
    {
        if($menuItem->getChildrencount() > 0)
        {
            foreach ($menuItem->getChildren() as $child)
            {
                $this->recursiveDeleteMenuItem($child, $em);
            }
        }
        $em->remove($menuItem);
        $em->flush();
    }
    /**
     * @Route("/admin/menus/{menu}/addItem", name="addMenuItem")
     */
    public function addMenuItem(MenuEntity $menu, Request $request, Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('add_menu_item', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Menus', '/admin/menus');
        $breadcrumbs->addBreadcrumb($menu->getTitle(), '/admin/menus/'.$menu->getId());
        $breadcrumbs->setActive('Add Menu Item');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $parentMenuItems = $em->getRepository(MenuItemEntity::class)->findBy(['menu'=>$menu->getId(), 'parent'=>null]);
        $typeChoices = array();
        $typeChoices['None'] = 0;
        $typeChoices = $this->buildRecursiveList($parentMenuItems, true);
        
        $categoryChoices = array();
        $categoryChoices['None'] = 0;
        $categories = $em->getRepository(CategoryGroup::class)->findBy(['system'=>0]);
        
        foreach($categories as $category)
        {
            $categoryChoices[$category->getTitle()] = $category->getId();
        }
        
        $hasCatChoices = array();
        $hasCatChoices['No'] = 0;
        $hasCatChoices['Yes'] = 1;
        
        //priority choices
        $priorityChoices = array();
        for($i = 0; $i <= 10; $i++)
        {
            $priorityChoices[$i] = $i;
        }
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();
        $form = $formFactory->createBuilder()
            ->add('title', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Title',
                    'class' => 'form-control',
                    'name' => 'title'
                )
            ))
            ->add('url', TextType::class,array(
                'required'=>false,
                'attr' =>array(
                    'placeholder'=>'Url',
                    'class'=>'form-control',
                    'name'=>'url'
                )
            ))
            ->add('parent', ChoiceType::class, array(
                'choices'=> $typeChoices,
                'attr' => array(
                    'class' => 'form-control',
                    'name' => 'parent'
                )
            ))
                ->add('priority', ChoiceType::class, array(
                'choices'=> $priorityChoices,
                'multiple' => false,
                'attr' => array(
                    'class' => 'form-control',
                    'name' => 'priority'
                )
            ))
            ->add('category', ChoiceType::class, array(
                'choices'=> $categoryChoices,
                'attr' => array(
                    'class' => 'form-control',
                    'name' => 'category'
                )
            ))
            ->add('hascategory', ChoiceType::class, array(
                'choices'=> $hasCatChoices,
                'attr' => array(
                    'class' => 'form-control',
                    'name' => 'hascategory'
                )
            ))
            ->getForm();
            $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $parent = $form->get('parent')->getData();
            $catId = $form->get('category')->getData();
            $hasCat = $form->get('hascategory')->getData();
            $menuItem = new MenuItemEntity();
            $menuItem->setMenu($menu);
            $menuItem->setTitle($form->get('title')->getData());
            $menuItem->setUrl($form->get('url')->getData());
            $menuItem->setSystem(false);
            $menuItem->setHascategory($hasCat);
            if($catId != 0)
            {
                $cat = $em->getRepository(CategoryGroup::class)->find($catId);
                $menuItem->setCategoryGroup($cat);
            }
            else
                $menuItem->setCategoryGroup (null);
            if($parent != 0)
            {
                $pt = $em->getRepository(MenuItemEntity::class)->find($parent);
                $menuItem->setParent($pt);
            }
            else
                $menuItem->setParent (null);
            $em->persist($menuItem);
            $em->flush();
            
            $this->addFlash('notice', 'Menu Item: ' . $menuItem->getTitle() . ' was added to Menu: '. $menu->getTitle() . ' successfully!');
            return $this->redirectToRoute('viewMenu', array('menu'=>$menu->getId()));
        }
        
        return $this->render('menus/add-menu-item.html.twig', array(
            'menu'=>$menu,
            'form'=>$form->createView(),
        ));
    }
    
    /**
     * @Route("/admin/menus/{menu}/{menuItem}/edit", name="editMenuItem")
     */
    public function editMenuItem(MenuEntity $menu, MenuItemEntity $menuItem, Request $request, Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('edit_menu_item', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Menus', '/admin/menus');
        $breadcrumbs->addBreadcrumb($menu->getTitle(), '/admin/menus/'.$menu->getId());
        $breadcrumbs->setActive('Edit Menu Item');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $menuItems = $em->getRepository(MenuItemEntity::class)->findBy(['menu'=>$menu->getId(), 'parent'=>null]);
        $typeChoices = array();
        $categoryChoices = array();
        $categoryChoices['None'] = 0;
        $categories = $em->getRepository(CategoryGroup::class)->findBy(['system'=>0]);
        $typeChoices['None'] = 0;
        $catVal = 0;
        $hasCatVal = $menuItem->getHascategory();
        $hasCatChoices = array();
        $hasCatChoices['No'] = 0;
        $hasCatChoices['Yes'] = 1;
        if($menuItem->getCategorygroup() != null)
        {
            $catVal = $menuItem->getCategorygroup()->getId();
        }
        foreach($categories as $category)
        {
            $categoryChoices[$category->getTitle()] = $category->getId();
        }
        $val = '';
        if($menuItem->getParent() == null)
            $val .= '0';
        else
            $val .= $menuItem->getParent()->getId();
        $typeChoices = $this->buildRecursiveList($menuItems, true, $menuItem);
        if($menuItem->getChildrencount() == 0)
        {
            foreach($menuItems as $mI)
            {
                if($mI->getTitle() != $menuItem->getTitle())
                    $typeChoices[$mI->getTitle()] = $mI->getId();
            }
        }
        $parentId = 0;
        $parent = $menuItem->getParent();
        if($parent != null)
            $parentId = $parent->getId();
        //priority choices
        $priorityChoices = array();
        for($i = 0; $i <= 10; $i++)
        {
            $priorityChoices[$i] = $i;
        }
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();
        $form = $formFactory->createBuilder()
            ->add('title', TextType::class, array(
                'data'=>$menuItem->getTitle(),
                'attr' => array(
                    'placeholder' => 'Title',
                    'class' => 'form-control',
                    'name' => 'title'
                )
            ))
            ->add('url', TextType::class,array(
                'data'=>$menuItem->getUrl(),
                'required'=>false,
                'attr' =>array(
                    'placeholder'=>'Url',
                    'class'=>'form-control',
                    'name'=>'url'
                )
            ))
            ->add('priority', ChoiceType::class, array(
                'choices'=> $priorityChoices,
                'multiple' => false,
                'attr' => array(
                    'class' => 'form-control',
                    'name' => 'priority'
                ),
                'data'=>$menuItem->getPriority()
            ))
            ->add('parent', ChoiceType::class, array(
                'data'=>$parentId,
                'choices'=> $typeChoices,
                'attr' => array(
                    'class' => 'form-control',
                    'name' => 'parent'
                )
            ))
            ->add('hascategory', ChoiceType::class, array(
                'data'=>$hasCatVal,
                'choices'=> $hasCatChoices,
                'attr' => array(
                    'class' => 'form-control',
                    'name' => 'hascategory'
                )
            ))
            ->add('category', ChoiceType::class, array(
                'data'=>$catVal,
                'choices'=> $categoryChoices,
                'attr' => array(
                    'class' => 'form-control',
                    'name' => 'category'
                )
            ))
            ->getForm();
            $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $parent = $form->get('parent')->getData();
            $catId = $form->get('category')->getData();
            $hasCat = $form->get('hascategory')->getData();
            
            $menuItem->setHascategory($hasCat);
            $menuItem->setMenu($menu);
            $menuItem->setTitle($form->get('title')->getData());
            $menuItem->setUrl($form->get('url')->getData());
            
            if($catId != 0)
            {
                $cat = $em->getRepository(CategoryGroup::class)->find($catId);
                $menuItem->setCategoryGroup($cat);
            }
            else
                $menuItem->setCategoryGroup(null);
            
            if($parent != 0)
            {
                $pt = $em->getRepository(MenuItemEntity::class)->find($parent);
                $menuItem->setParent($pt);
            }
            else
            {
                $menuItem->setParent(null);
            }
            $em->persist($menuItem);
            $em->flush();
            
            $this->addFlash('notice', 'Menu Item: ' . $menuItem->getTitle() . ' was edited successfully!');
            return $this->redirectToRoute('viewMenu', array('menu'=>$menu->getId()));
        }
        
        return $this->render('menus/edit-menu-item.html.twig', array(
            'menu'=>$menu,
            'menuItem'=>$menuItem,
            'form'=>$form->createView(),
        ));
    }
    
    /**
     * @Route("/admin/menus/{menu}/{menuItem}/delete", name="deleteMenuItem")
     */
    public function deleteMenuItem(MenuEntity $menu, MenuItemEntity $menuItem, Request $request, Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('delete_menu_item', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Menus', '/admin/menus');
        $breadcrumbs->addBreadcrumb($menu->getTitle(), '/admin/menus/'.$menu->getId());
        $breadcrumbs->setActive('Delete Menu Item');
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('hidden', HiddenType::class, array(
                    
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            if($menuItem->getChildrencount() > 0)
            {
                foreach($menuItem->getChildren() as $child)
                {
                    $child->setParent(null);
                    $menuItem->removeChild($child);
                    $em->persist($child);
                    $em->flush();
                }
            }
            $em->remove($menuItem);
            $em->flush();
            
            $this->addFlash('notice', 'Menu Item: '.$menuItem->getTitle() . ' was removed successfully!');
            return $this->redirectToRoute('viewMenu', array('menu'=>$menu->getId()));
        }
        return $this->render('menus/delete-menu-item.html.twig', array(
            'menu' => $menu,
            'menuItem' => $menuItem,
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Route("/admin/menus/{menu}", name="viewMenu")
     */
    public function viewMenu(MenuEntity $menu, Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('admin_view_menus', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Menus', '/admin/menus');
        $breadcrumbs->setActive($menu->getTitle());
        $breadcrumbs->setBreadcrumbs();
        
        return $this->render('menus/view-menu.html.twig', array(
            'menu'=>$menu,
        ));
    }
    
    private function buildRecursiveList($menuItems, bool $firstRun = false, MenuItemEntity $defualtMenuItem = null, bool $parent = true, $var = null, int $count = 0)
    {
        if($var == null)
            $var = array();
        if($firstRun == true)
            $var['NONE'] = 0;
        foreach($menuItems as $menuItem)
        {
            if($defualtMenuItem != null && $defualtMenuItem->getTitle() == $menuItem->getTitle())
                continue;
            $beginingString = '';
            for($i=0;$i<$count;$i++)
            {
                $beginingString .= '-';
            }
            $var[$beginingString . $menuItem->getTitle()] = $menuItem->getId();
            $children = $menuItem->getChildren();
            
            if($children instanceof \Doctrine\ORM\PersistentCollection && $menuItem->getChildrencount() > 0)
            {
                $newCount = $count + 1;
                $var = $this->buildRecursiveList($children, false, $defualtMenuItem, false, $var, $newCount);
            }
        }
        return $var;
    }
}
