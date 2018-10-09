<?php

namespace App\Controller;

use App\Service\Breadcrumbs;
use App\Service\PrettyPhoto;
use App\Service\FileUploaderUtility;
use App\Form\Type\JQueryFileUploaderType;
use App\Form\Type\CKEditorType;
use App\Entity\User;
use App\Entity\RoleEntity;
use App\Entity\FileEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    /**
     * @Route("/admin/jot1", name="jotone")
     */
    public function jotone(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('view_jot_forms', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->setActive('Jot Form 1');
        $breadcrumbs->setBreadcrumbs();
        
        return $this->render('admin/jot.html.twig', array(
        ));
    }
    /**
     * @Route("/admin/jot2", name="jottwo")
     */
    public function jottwo(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('view_jot_forms', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->setActive('Jot Form 2');
        $breadcrumbs->setBreadcrumbs();
        
        return $this->render('admin/jot_form_2.html.twig', array(
        ));
    }
    /**
     * @Route("/admin/users", name="viewUsers")
     */
    public function viewUsers(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
         // The second parameter is used to specify on what object the role is tested.
        $this->denyAccessUnlessGranted('view_all_users', null, 'Unable to access this page!');
        
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->setActive('Users');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        // replace this line with your own code!
        return $this->render('user/view-users.html.twig', array(
            'users' => $users
        ));
    }
    
    /**
     * @Route("/admin/users/{user}/roles", name="editUserRoles")
     */
    public function editUserRoles(User $user, Breadcrumbs $breadcrumbs, Request $request)
    {
        parent::hideProfiler($this->getUser());
         // The second parameter is used to specify on what object the role is tested.
        $this->denyAccessUnlessGranted('edit_user_roles', null, 'Unable to access this page!');
        
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Home', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Users', '/admin/users');
        $breadcrumbs->setActive('Edit '.$user->getUsername().'\'s roles');
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $formFactory = $formFactory->createBuilder();
        
        $em = $this->getDoctrine()->getManager();
        $roles = $em->getRepository(RoleEntity::class)->findAll();
        foreach($roles as $role)
        {
            if($role->getMachinetitle() == 'ROLE_USER')
            {
                continue;
            }
            if($user->hasRole($role))
            {
                $formFactory->add('role_'.$role->getId(), CheckboxType::class, array(
                    'data' => true,
                    'label' => $role->getTitle(),
                    'required' => false,
                    'empty_data' => true
                ));
            }
            else
            {
                $formFactory->add('role_'.$role->getId(), CheckboxType::class, array(
                    'data' => false,
                    'label' => $role->getTitle(),
                    'required' => false,
                    'empty_data' => true
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
            foreach($roles as $role)
            {
                if($role->getMachinetitle() == 'ROLE_USER')
                    continue;
                //remove all roles from user
                $user->removeRole($role);
            }
            //next itterate the roles again to get the form data and add the roles that are true
            foreach($roles as $role)
            {
                if($role->getMachinetitle() == 'ROLE_USER')
                    continue;
                $roleData = $form->get('role_'.$role->getId())->getData();
                if($roleData == true)
                    $user->addRole($role);
            }
            $em->persist($user);
            $em->flush();
            $this->addFlash('notice', $user->getUsername() . '\'s roles saved successfully!!');
            return $this->redirectToRoute('viewUsers');
        }
        
        // replace this line with your own code!
        return $this->render('user/edit-user-roles.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
            'roles' => $roles
        ));
    }
    
    /**
     * @Route("/admin/users/{user}/delete", name="deleteUser")
     */
    public function deleteUser(User $user, Breadcrumbs $breadcrumbs, Request $request)
    {
        parent::hideProfiler($this->getUser());
        // The second parameter is used to specify on what object the role is tested.
        $this->denyAccessUnlessGranted('delete_users', null, 'Unable to access this page!');
        
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Home', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Users', '/admin/users');
        $breadcrumbs->setActive('Delete '.$user->getUsername().'\'s account');
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
            
            $em->remove($user);
            $em->flush();
            $this->addFlash('notice', 'User '.$user->getUsername() . ' was deleted successfully!');
            return $this->redirectToRoute('viewUsers');
        }
        return $this->render('user/delete-user.html.twig',array(
            'form'=>$form->createView(),
            'user'=>$user
        ));
    }
    
    /**
     * @Route("/account", name="account")
     */
    public function account(Breadcrumbs $breadcrumbs, Request $request, PrettyPhoto $prettyPhoto, FileUploaderUtility $fileUploaderUtil)
    {
        parent::hideProfiler($this->getUser());
         // The second parameter is used to specify on what object the role is tested.
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');
        
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->setActive('Account');
        $breadcrumbs->setBreadcrumbs();
        
        
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $files = $user->getFiles();
        $existingFiles = $fileUploaderUtil->makeExistingFilesArray($files, $this->getParameter('upload_directory'));
        $form = $formFactory->createBuilder()
                ->add('bio', CKEditorType::class, array(
                    'attr' => array(
                        'placeholder' => 'A Little About You',
                        'class' => 'form-control',
                        'name' => 'bio'
                    ),
                    'data'=>$user->getBio(),
                    'id'=>'form_bio',
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                ))
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'PrettyPhoto'=>$prettyPhoto,
                    'uploadUrl'=>'/file/upload/user/users/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'maxNumberOfFiles'=>1,
                    'fileTypes'=>array(
                        'png','jpg'
                    ),
                    'id'=>'form_postfile',
                    'label'=>'Avatar',
                    'data_class'=> User::class,
                    'required'=>false,
                    'existingFiles'=>$existingFiles,
                    'attr'=>array(
                        'class' => 'form-control',
                        'name' => 'postfile',
                    )
                ))
                ->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class,array(
                    'attr'=>array(
                        'class'=>'btn btn-common btn-md pull-right'
                    )
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $userFiles = $request->get('postfiles');
            if(is_array($userFiles))
            {
                foreach($userFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                    {
                        $user->addFile($fileEntity);
                        $user->setAvatar($fileEntity->getUrl());
                    }
                }
            }
            $user->setBio($form->get('bio')->getData());
            $em->persist($user);
            $em->flush();
            $this->addFlash('notice', 'Your account was saved');
                return $this->redirectToRoute('account');
        }
        return $this->render('user/account.html.twig', array(
            'user' => $user,
            'settingsForm'=>$form->createView()
        ));
    }
    
    /**
     * @Route("/admin/user/{$username}", name="viewUser")
     */
    public function viewUser(Breadcrumbs $breadcrumbs)
    {
        return $this->render('');
    }
    
    
}
