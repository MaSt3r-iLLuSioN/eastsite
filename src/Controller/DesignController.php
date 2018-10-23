<?php

namespace App\Controller;

use \stdClass;
use App\Service\Breadcrumbs;
use App\Entity\DesignerFloorTexture;
use App\Entity\DesignerWallTexture;
use App\Entity\DesignerModel;
use App\Service\Fotorama;
use App\Service\PrettyPhoto;
use App\Service\FileUploaderUtility;
use App\Entity\CategoryGroup;
use App\Entity\CategoryEntity;
use App\Entity\FileEntity;
use App\Form\Type\JQueryFileUploaderType;
use App\Form\Type\CKEditorType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


use Symfony\Component\Routing\Annotation\Route;

class DesignController extends BaseController
{
    /**
     * Shows our awesome home designer
     * @Route("/design", name="design")
     */
    public function index(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->setActive('Home Designer', '/design');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        //get wall texture categories and all wall textures
        $wallcategoryGroup = $em->getRepository(CategoryGroup::class)->findBy(['title'=>'Designer Walls']);
        $wallcategories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$wallcategoryGroup[0]]);
        $unsortedWallTextures = $em->getRepository(DesignerWallTexture::class)->findAll();
        $sortedWallTextures = array();
        foreach($wallcategories as $cat)
        {
            $sortedCats = new stdClass();
            $sortedCats->title = $cat->getTitle();
            $foundTexture = false;
            foreach($unsortedWallTextures as $unsort)
            {
                if($cat->getId() == $unsort->getCategory()->getId())
                {
                    $sortment = new stdClass();
                    $files = $unsort->getFiles();
                    $sortment->url = $files[0]->getUrl();
                    $sortment->title = $unsort->getTitle(); 
                    $sortment->stretch = $unsort->getStretch();
                    $sortment->scale = $unsort->getScale();
                    $sortedCats->textures[] = $sortment;
                    $sortedCats->texture = true;
                    $foundTexture = true;
                    
                }
               
            }
            if(!$foundTexture)
                $sortedCats->texture = false;
            $sortedWallTextures[] = $sortedCats;
        }
        
        return $this->render('design/index.html.twig', [
            'controller_name' => 'DesignController',
            'wallTextures'=>$sortedWallTextures
        ]);
    }
    /**
     * list statistics for designer textures and models
     * @Route("/admin/designer", name="adminDesigner")
     */
    public function adminDesigner(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->setActive('Home Designer', '/admin/designer');
        $breadcrumbs->setBreadcrumbs();
        return $this->render('design/wall-textures.html.twig',array(
        ));
    }
    /**
     * Adds models to the designer
     * @Route("/admin/designer/models/add", name="addDesignerModel")
     */
    public function addModel(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->addBreadcrumb('Models', '/admin/designer/models');
        $breadcrumbs->setActive('Add Model', '/admin/designer/models/add');
        $breadcrumbs->setBreadcrumbs();
        return $this->render('design/wall-textures.html.twig',array(
        ));
    }
    /**
     * deletes a model from the designer
     * @Route("/admin/designer/models/delete", name="deleteDesignerModel")
     */
    public function deleteModel(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->addBreadcrumb('Models', '/admin/designer/models');
        $breadcrumbs->setActive('Delete Model', '/admin/designer/models/delete');
        $breadcrumbs->setBreadcrumbs();
        return $this->render('design/wall-textures.html.twig',array(
        ));
    }
    /**
     * list designer models for easy CRUD functionalities
     * @Route("/admin/designer/models", name="listDesignerModels")
     */
    public function listModels(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->setActive('Models', '/admin/designer/models');
        $breadcrumbs->setBreadcrumbs();
        return $this->render('design/wall-textures.html.twig',array(
        ));
    }
    
    /**
     * Adds wall textures to the designer
     * @Route("/admin/designer/walls/add", name="addDesignerWall")
     */
    public function addWallTexture(Breadcrumbs $breadcrumbs, Request $request, PrettyPhoto $prettyPhoto)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->addBreadcrumb('Wall Textures', '/admin/designer/walls');
        $breadcrumbs->setActive('Add Wall Texture', '/admin/designer/walls/add');
        $breadcrumbs->setBreadcrumbs();
        $em = $this->getDoctrine()->getManager();
        $categoryGroup = $em->getRepository(CategoryGroup::class)->findBy(['title'=>'Designer Walls']);
        $categories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$categoryGroup[0]]);
        $choices = array();
        foreach($categories as $category)
        {
            $choices[$category->getTitle()] = $category->getId();
        }
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Title For Wall Texture',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('category', ChoiceType::class, array(
                    'choices'=> $choices,
                    'attr' => array(
                        'class' => 'form-control contact-control',
                        'name' => 'category'
                    )
                ))
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'PrettyPhoto'=>$prettyPhoto,
                    'uploadUrl'=>'/file/upload/walltexture/walltextures/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'maxNumberOfFiles' => 1,
                    'fileTypes'=>array(
                        'png','jpg'
                    ),
                    'id'=>'form_postfile',
                    'label'=>'Post File',
                    'data_class'=> DesignerWallTexture::class,
                    'required'=>false,
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
            $wallTexture = new DesignerWallTexture();
            $postFiles = $request->get('postfiles');
            if(is_array($postFiles))
            {
                foreach($postFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                    $wallTexture->addFile($fileEntity);
                }
            }
            
            $wallTexture->setTitle($form->get('title')->getData());
            $category = $em->getRepository(CategoryEntity::class)->findBy(['id'=>$form->get('category')->getData()]);
            $wallTexture->setCategory($category[0]);
            $wallTexture->setScale('300');
            $wallTexture->setStretch(false);
            //get uploaded file name(s)
            //$file = $form->get('image')->getData();
            

            //$post->setImage($fileName);
            $em->persist($wallTexture);
            $em->flush();
            $this->addFlash('success', 'Wall Texture: ' . $wallTexture->getTitle() . ' was created successfully!');
                return $this->redirectToRoute('listDesignerWalls');
        }
        return $this->render('design/add-edit-texture.html.twig',array(
            'form'=>$form->createView(),
            'title'=>'Home Designer: Add Wall Texture',
            'action'=>'/admin/designer/walls/add',
            'cancel'=>'/admin/designer/walls'
        ));
    }
    
    /**
     * edits wall texture from designer
     * @Route("/admin/designer/walls/edit/{wallTexture}", name="editDesignerWall")
     */
    public function editWallTexture(Breadcrumbs $breadcrumbs, Request $request, PrettyPhoto $prettyPhoto, DesignerWallTexture $wallTexture,FileUploaderUtility $fileUploaderUtil)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->addBreadcrumb('Wall Textures', '/admin/designer/walls');
        $breadcrumbs->setActive('Edit Wall Texture');
        $breadcrumbs->setBreadcrumbs();
        $em = $this->getDoctrine()->getManager();
        $categoryGroup = $em->getRepository(CategoryGroup::class)->findBy(['title'=>'Designer Walls']);
        $categories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$categoryGroup[0]]);
        $choices = array();
        foreach($categories as $category)
        {
            $choices[$category->getTitle()] = $category->getId();
        }
        $files = $wallTexture->getFiles();
        $existingFiles = $fileUploaderUtil->makeExistingFilesArray($files, $this->getParameter('upload_directory'));
        $category = $wallTexture->getCategory();
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Title For Wall Texture',
                        'class' => 'form-control',
                        'name' => 'title'
                    ),
                    'data'=>$wallTexture->getTitle()
                ))
                ->add('category', ChoiceType::class, array(
                    'choices'=> $choices,
                    'attr' => array(
                        'class' => 'form-control contact-control',
                        'name' => 'category'
                    ),
                    'data'=>$category->getId()
                ))
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'PrettyPhoto'=>$prettyPhoto,
                    'uploadUrl'=>'/file/upload/walltexture/walltextures/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'maxNumberOfFiles' => 1,
                    'existingFiles'=>$existingFiles,
                    'fileTypes'=>array(
                        'png','jpg'
                    ),
                    'id'=>'form_postfile',
                    'label'=>'Post File',
                    'data_class'=> DesignerWallTexture::class,
                    'required'=>false,
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
            foreach($files as $file)
            {
                //remove all files connected to walltexture
                $wallTexture->removeFile($file);
                
            }
            
            $textureFiles = $request->get('postfiles');
            if(is_array($textureFiles))
            {
                foreach($textureFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                        $wallTexture->addFile($fileEntity);
                }
            }
            
            $wallTexture->setTitle($form->get('title')->getData());
            $category = $em->getRepository(CategoryEntity::class)->findBy(['id'=>$form->get('category')->getData()]);
            $wallTexture->setCategory($category[0]);
            $wallTexture->setScale('300');
            $wallTexture->setStretch(false);
            $em->persist($wallTexture);
            $em->flush();
            $this->addFlash('success', 'Wall Texture: ' . $wallTexture->getTitle() . ' was edited successfully!');
                return $this->redirectToRoute('listDesignerWalls');
        }
        return $this->render('design/add-edit-texture.html.twig',array(
            'form'=>$form->createView(),
            'title'=>'Home Designer: Edit Wall Texture',
            'action'=>'/admin/designer/walls/edit/'.$wallTexture->getId(),
            'cancel'=>'/admin/designer/walls'
        ));
    }
    
    /**
     * deletes wall texture from the designer
     * @Route("/admin/designer/walls/delete/{wallTexture}", name="deleteDesignerWall")
     */
    public function deleteWallTexture(Breadcrumbs $breadcrumbs, DesignerWallTexture $wallTexture)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->addBreadcrumb('Wall Textures', '/admin/designer/walls');
        $breadcrumbs->setActive('Delete Wall Texture');
        $breadcrumbs->setBreadcrumbs();
        return $this->render('design/wall-textures.html.twig',array(
        ));
    }
    
    /**
     * list designer wall textures
     * @Route("/admin/designer/walls", name="listDesignerWalls")
     */
    public function listWallTextures(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->setActive('Wall Textures');
        $breadcrumbs->setBreadcrumbs();
        return $this->render('design/wall-textures.html.twig',array(
        ));
    }
    
    /**
     * Adds floor textures to the designer
     * @Route("/admin/designer/floors/add", name="addDesignerFloor")
     */
    public function addFloorTexture(Breadcrumbs $breadcrumbs, Request $request, PrettyPhoto $prettyPhoto)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->addBreadcrumb('Floor Textures', '/admin/designer/floors');
        $breadcrumbs->setActive('Add Floor Texture');
        $breadcrumbs->setBreadcrumbs();
        $em = $this->getDoctrine()->getManager();
        $categoryGroup = $em->getRepository(CategoryGroup::class)->findBy(['title'=>'Designer Floors']);
        $categories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$categoryGroup[0]]);
        $choices = array();
        foreach($categories as $category)
        {
            $choices[$category->getTitle()] = $category->getId();
        }
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Title For Floor Texture',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('category', ChoiceType::class, array(
                    'choices'=> $choices,
                    'attr' => array(
                        'class' => 'form-control contact-control',
                        'name' => 'category'
                    )
                ))
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'PrettyPhoto'=>$prettyPhoto,
                    'uploadUrl'=>'/file/upload/floortexture/floortextures/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'maxNumberOfFiles' => 1,
                    'fileTypes'=>array(
                        'png','jpg'
                    ),
                    'id'=>'form_postfile',
                    'label'=>'Post File',
                    'data_class'=> DesignerFloorTexture::class,
                    'required'=>false,
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
            $floorTexture = new DesignerFloorTexture();
            $postFiles = $request->get('postfiles');
            if(is_array($postFiles))
            {
                foreach($postFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                    $floorTexture->addFile($fileEntity);
                }
            }
            
            $floorTexture->setTitle($form->get('title')->getData());
            $category = $em->getRepository(CategoryEntity::class)->findBy(['id'=>$form->get('category')->getData()]);
            $floorTexture->setCategory($category[0]);
            $floorTexture->setScale('300');
            $floorTexture->setStretch(false);
            $em->persist($floorTexture);
            $em->flush();
            $this->addFlash('success', 'Floor Texture: ' . $floorTexture->getTitle() . ' was created successfully!');
                return $this->redirectToRoute('listDesignerFloors');
        }
        return $this->render('design/add-edit-texture.html.twig',array(
            'form'=>$form->createView(),
            'title'=>'Home Designer: Add Floor Texture',
            'action'=>'/admin/designer/floors/add',
            'cancel'=>'/admin/designer/floors'
        ));
    }
    /**
     * edit floor textures from the designer
     * @Route("/admin/designer/floors/edit/{floorTexture}", name="editDesignerFloor")
     */
    public function editFloorTexture(Breadcrumbs $breadcrumbs, Request $request, PrettyPhoto $prettyPhoto, DesignerFloorTexture $floorTexture,FileUploaderUtility $fileUploaderUtil)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->addBreadcrumb('Floor Textures', '/admin/designer/floors');
        $breadcrumbs->setActive('Edit Floor Texture');
        $em = $this->getDoctrine()->getManager();
        $categoryGroup = $em->getRepository(CategoryGroup::class)->findBy(['title'=>'Designer Floors']);
        $categories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$categoryGroup[0]]);
        $choices = array();
        foreach($categories as $category)
        {
            $choices[$category->getTitle()] = $category->getId();
        }
        $files = $floorTexture->getFiles();
        $existingFiles = $fileUploaderUtil->makeExistingFilesArray($files, $this->getParameter('upload_directory'));
        $category = $floorTexture->getCategory();
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Title For Wall Texture',
                        'class' => 'form-control',
                        'name' => 'title'
                    ),
                    'data'=>$floorTexture->getTitle()
                ))
                ->add('category', ChoiceType::class, array(
                    'choices'=> $choices,
                    'attr' => array(
                        'class' => 'form-control contact-control',
                        'name' => 'category'
                    ),
                    'data'=>$category->getId()
                ))
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'PrettyPhoto'=>$prettyPhoto,
                    'uploadUrl'=>'/file/upload/walltexture/walltextures/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'maxNumberOfFiles' => 1,
                    'existingFiles'=>$existingFiles,
                    'fileTypes'=>array(
                        'png','jpg'
                    ),
                    'id'=>'form_postfile',
                    'label'=>'Post File',
                    'data_class'=> DesignerFloorTexture::class,
                    'required'=>false,
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
            foreach($files as $file)
            {
                //remove all files connected to walltexture
                $floorTexture->removeFile($file);
                
            }
            
            $textureFiles = $request->get('postfiles');
            if(is_array($textureFiles))
            {
                foreach($textureFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                        $floorTexture->addFile($fileEntity);
                }
            }
            
            $floorTexture->setTitle($form->get('title')->getData());
            $category = $em->getRepository(CategoryEntity::class)->findBy(['id'=>$form->get('category')->getData()]);
            $floorTexture->setCategory($category[0]);
            $floorTexture->setScale('300');
            $floorTexture->setStretch(false);
            $em->persist($floorTexture);
            $em->flush();
            $this->addFlash('success', 'Floor Texture: ' . $floorTexture->getTitle() . ' was edited successfully!');
                return $this->redirectToRoute('listDesignerFloors');
        }
        return $this->render('design/add-edit-texture.html.twig',array(
            'form'=>$form->createView(),
            'title'=>'Home Designer: Edit Floor Texture',
            'action'=>'/admin/designer/floors/edit/'.$floorTexture->getId(),
            'cancel'=>'/admin/designer/floors'
        ));
    }
    /**
     * delete floor textures from the designer
     * @Route("/admin/designer/floors/delete", name="deleteDesignerFloor")
     */
    public function deleteFloorTexture(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->addBreadcrumb('Floor Textures', '/admin/designer/floors');
        $breadcrumbs->setActive('Delete Floor Texture');
        $breadcrumbs->setBreadcrumbs();
        return $this->render('design/wall-textures.html.twig',array(
        ));
    }
    /**
     * list floor textures of the designer
     * @Route("/admin/designer/floors", name="listDesignerFloors")
     */
    public function listFloorTextures(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Home Designer', '/admin/designer');
        $breadcrumbs->setActive('Floor Textures');
        $breadcrumbs->setBreadcrumbs();
        return $this->render('design/wall-textures.html.twig',array(
        ));
    }   
}
