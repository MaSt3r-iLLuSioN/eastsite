<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\RoleEntity;
use App\Entity\PermissionEntity;
use App\Service\Breadcrumbs;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\HttpFoundation\Request;

class ACLController extends BaseController
{
    /**
     * @Route("/admin/roles", name="roles")
     */
    public function roles(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('admin_view_roles', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Roles');
        $breadcrumbs->setBreadcrumbs();
        $em = $this->getDoctrine()->getManager();
        //get all regions
        $roles = $em->getRepository(RoleEntity::class)->findAll();
        
        $siteRoles = array();
        
        $catGroup = $em->getRepository(\App\Entity\CategoryGroup::class)->findBy(['title'=> 'Permissions Group']);
        $categories = $catGroup[0]->getCategories();
        $groupCategories = array();
        foreach($categories as $category)
        {
            $categoryPermissions = array();
            $permissions = $em->getRepository(PermissionEntity::class)->findBy(['category'=>$category],['title'=>'ASC']);
            foreach($permissions as $permission)
            {
                $categoryPermissions[] = array(
                    'id'=>$permission->getId(),
                    'title'=>$permission->getTitle(),
                    'hasPermission'=>false
                );
            }
            $groupCategories[] = array(
                'title'=>$category->getTitle(),
                'permissions'=>$categoryPermissions
            );
        }
        foreach($roles as $role)
        {
            foreach($groupCategories as $catKey=>$category)
            {
                foreach($groupCategories[$catKey]['permissions'] as $permKey=>$permission)
                {
                    if($role->hasPermissionTitle($permission['title']))
                    {
                        $groupCategories[$catKey]['permissions'][$permKey]['hasPermission'] = true;
                    }
                    else
                    {
                        $groupCategories[$catKey]['permissions'][$permKey]['hasPermission'] = false;
                    }
                }
            }
            $siteRoles[] = array(
                'title'=>$role->getTitle(),
                'id'=>$role->getId(),
                'system'=>$role->getSystem(),
                'categories' => $groupCategories
            );
        }
            
        
        // replace this line with your own code!
        return $this->render('ACL/roles.html.twig', array(
            'roles'=>$siteRoles
        ));
    }
    
    /**
     * @Route("/admin/roles/add", name="addRole")
     */
    public function addRole(Breadcrumbs $breadcrumbs, Request $request)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('add_role', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Roles', '/admin/roles');
        $breadcrumbs->setActive('Add Role');
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Role Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $roleEntity = new RoleEntity();
            $roleEntity->setTitle($form->get('title')->getData());
            $title = $roleEntity->getTitle();
            $machineTitle = strtoupper(str_replace(' ','_',$title));
            $roleEntity->setMachinetitle($machineTitle);
            $roleEntity->setSystem(false);
            if($roleEntity->roleExists($roleEntity->getMachinetitle(), $em))
            {
                $this->addFlash('warning', 'Role: ' . $roleEntity->getTitle() . ' already exists!');
                return $this->redirectToRoute('roles');
            }
            $em->persist($roleEntity);
            $em->flush();
            $this->addFlash('notice', 'Role "' . $roleEntity->getTitle() . '" was made successfully. Dont forget to edit the permissions!');
            return $this->redirectToRoute('roles');
        }
        return $this->render('ACL/add-role.html.twig',array(
            'form'=>$form->createView()
        ));
    }
    
    /**
     * @Route("/admin/roles/{role}/edit", name="editRole")
     */
    public function editRole(RoleEntity $role, Breadcrumbs $breadcrumbs, Request $request)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('edit_role', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Roles', '/admin/roles');
        $breadcrumbs->setActive('Edit Role "'.$role->getTitle().'"');
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();
        $form = $formFactory->createBuilder()
            ->add('title', TextType::class, array(
                'data' => $role->getTitle(),
                'attr' => array(
                    'placeholder' => 'Role Title',
                    'class' => 'form-control',
                    'name' => 'title'
                )
            ))
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $role->setTitle($form->get('title')->getData());
            $title = $role->getTitle();
            $machineTitle = strtoupper(str_replace(' ','_',$title));
            $role->setMachinetitle($machineTitle);
            $role->setSystem(false);
            $em->persist($role);
            $em->flush();
            $this->addFlash('notice', 'Role "' . $role->getTitle() . '" was edited successfully.');
            return $this->redirectToRoute('roles');
        }
        return $this->render('ACL/edit-role.html.twig',array(
            'form'=>$form->createView(),
            'role'=>$role
        ));
    }
    
    /**
     * @Route("/admin/roles/{role}/delete", name="deleteRole")
     */
    public function deleteRole(RoleEntity $role, Breadcrumbs $breadcrumbs, Request $request)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('delete_role', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Roles', '/admin/roles');
        $breadcrumbs->setActive('Delete Role "' . $role->getTitle().'"');
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
            $permissions = $role->getPermissions();
            foreach($permissions as $permission)
            {
                $role->removePermission($permission);
            }
            //remove this role from any users that have it
            $users = $role->getUsers();
            foreach($users as $user)
            {
                $user->removeRole($role);
                $em->persist($user);
                $em->flush();
            }
            $em->remove($role);
            $em->flush();
            $this->addFlash('notice', 'Role "'.$role->getTitle() . '" was deleted successfully!');
            return $this->redirectToRoute('roles');
        }
        return $this->render('ACL/delete-role.html.twig',array(
            'form'=>$form->createView(),
            'role'=>$role
        ));
    }
    
    /**
     * @Route("/admin/roles/{role}/permissions", name="rolePermissions")
     */
    public function rolePermissions(RoleEntity $role, Breadcrumbs $breadcrumbs, Request $request)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('edit_role_permissions', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Roles','/admin/roles');
        $breadcrumbs->setActive('Edit Role "'.$role->getTitle() . '" Permissions');
        $breadcrumbs->setBreadcrumbs();
        //this is were we will get a custom form for the block type (makes it easier to add different types)
        $em = $this->getDoctrine()->getManager();
        
        $catGroup = $em->getRepository(\App\Entity\CategoryGroup::class)->findBy(['title'=> 'Permissions Group']);
        $categories = $catGroup[0]->getCategories();
        $groupCategories = array();
        foreach($categories as $category)
        {
            $categoryPermissions = array();
            $permissions = $em->getRepository(PermissionEntity::class)->findBy(['category'=>$category],['title'=>'ASC']);
            foreach($permissions as $permission)
            {
                $categoryPermissions[] = array(
                    'id'=>$permission->getId(),
                    'title'=>$permission->getTitle(),
                    'hasPermission'=>false,
                    'description'=>$permission->getDescription()
                );
            }
            $groupCategories[] = array(
                'title'=>$category->getTitle(),
                'permissions'=>$categoryPermissions
            );
        }
        
        foreach($groupCategories as $catKey=>$category)
        {
            foreach($groupCategories[$catKey]['permissions'] as $permKey=>$permission)
            {
                if($role->hasPermissionTitle($permission['title']))
                {
                    $groupCategories[$catKey]['permissions'][$permKey]['hasPermission'] = true;
                }
                else
                {
                    $groupCategories[$catKey]['permissions'][$permKey]['hasPermission'] = false;
                }
            }
        }
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $formFactory = $formFactory->createBuilder();        
        
        foreach($groupCategories as $category)
        {
            foreach($category['permissions'] as $permission)
            {
                $formFactory->add('permission_'.$permission['id'], CheckboxType::class, array(
                    'data'=>$permission['hasPermission'],
                    'label'=>$permission['title'],
                    'required'=>false,
                    'empty_data'=>true
                ));
            }
        }
        $formFactory->add('submit', SubmitType::class,array(
            'attr'=>array(
                'class'=>'btn btn-common btn-md pull-right'
            )
        ));
        $form = $formFactory->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            //we iterate through all permissions and remove them
            $permissions = $em->getRepository(PermissionEntity::class)->findAll();
            foreach($permissions as $permission)
            {
                $role->removePermission($permission);
            }
            //next we itterate the permissions again and get the form data and save any true values
            foreach($permissions as $permission)
            {
                $permData = $form->get('permission_'.$permission->getId())->getData();
                if($permData == true)
                {
                    $role->addPermission($permission);
                    
                }
                //skip all false because we already removed them all
                $em->persist($role);
                $em->flush();
            }
            $this->addFlash('notice', 'Role "'.$role->getTitle() . '" permissions saved successfully!!');
            return $this->redirectToRoute('roles');
        }
        return $this->render('ACL/permissions.html.twig',array(
            'form'=>$form->createView(),
            'categories'=>$groupCategories,
            'role'=>$role
        ));
        
    }
    
}