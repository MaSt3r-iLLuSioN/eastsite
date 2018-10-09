<?php

namespace App\Controller;
use App\Entity\PageEntity;
use App\Entity\LayoutEntity;
use App\Entity\FileEntity;
use App\Service\Breadcrumbs;
use App\Service\PrettyPhoto;
use App\Service\Fotorama;
use App\Service\KeywordHelper;
use App\Service\FileUploaderUtility;
use App\Service\HelperMethods;
use App\Form\Type\JQueryFileUploaderType;
use App\Form\Type\CKEditorType;
use App\Form\Type\AutoTagsType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\PersistentCollection;

class PageController extends BaseController
{
    /**
     * @Route("/admin/pages", name="viewPages")
     */
    public function viewPages(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('view_admin_page_area', null);
        //set breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->setActive('Pages');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(PageEntity::class)->findAll();
        return $this->render('page/view-pages.html.twig', array(
            'pages'=>$pages,
        ));
    }
    
    /**
     * @Route("/admin/pages/add", name="addPage")
     */
    public function addPage(Request $request,Breadcrumbs $breadcrumbs, PrettyPhoto $prettyPhoto, HelperMethods $helper, KeywordHelper $keywordHelper)
    {
        parent::hideProfiler($this->getUser());
        //only admin can add a site page
        $this->denyAccessUnlessGranted('add_page', null);
        //set breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Pages', '/admin/pages');
        $breadcrumbs->setActive('Add Page');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        //get available layouts
        $layouts = $em->getRepository(LayoutEntity::class)->findAll();
        $choices = array();
        foreach($layouts as $layout)
        {
            $choices[$layout->getType()] = $layout->getId();
        }
        $menu = $em->getRepository(\App\Entity\MenuEntity::class)->find(7);
        $mItems = $menu->getItems();
        $menuArray = array();
        $menuArray['None'] = 0;
        foreach($mItems as $item)
        {
            if($item->getParent() == null)
            {
                $menuArray[$item->getTitle()] = $item->getId();
                if ($item->getChildrencount() > 0)
                {
                    foreach($item->getChildren() as $child)
                    {
                        $menuArray = $helper->buildRecursiveMenuArray($child, $menuArray, 1);
                    }
                }
            }
        }
        //create form
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Page Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('content', CKEditorType::class, array(
                    'attr' => array(
                        'placeholder' => 'Page Content',
                        'class' => 'form-control',
                        'name' => 'content'
                    ),
                    'id'=>'form_content',
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                ))
                ->add('addmenu', ChoiceType::class, array(
                    'choices'=>array('Yes'=>1,'No'=>0),
                    'attr' => array(
                        'class'=>'form-control',
                        'name'=>'addmenu'
                    )
                ))
                ->add('menuparent', ChoiceType::class, array(
                    'choices'=>$menuArray,
                    'attr'=>array(
                        'class'=>'form-control',
                        'name'=>'menuparent'
                    )
                ))
                ->add('showsubpages', ChoiceType::class, array(
                    'choices'=>array(
                        'No'=>0,
                        'Yes'=>1
                    ),
                    'attr'=>array(
                        'class'=>'form-control',
                        'name'=>'showsubpages'
                    )
                ))
                ->add('metakeywords', AutoTagsType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'dataUrl'=>'/ajax/keywords',
                    'id'=>'metakeywords',
                    'attr' => array(
                        'placeholder' => 'Keywords',
                        'class' => 'form-control',
                        'name' => 'metakeywords'
                    )
                ))
                ->add('metadescription', TextareaType::class, array(
                    'attr' => array(
                        'placeholder'=>'Meta Description',
                        'class'=>'form-control',
                        'name'=>'metadescription'
                    )
                ))
                ->add('layout', ChoiceType::class, array(
                    'choices'=> $choices,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'layout'
                    )
                ))
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'PrettyPhoto'=>$prettyPhoto,
                    'uploadUrl'=>'/file/upload/page/test/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'fileTypes'=>array(
                        'png','jpg','jpeg','gif','pdf','docx','mp3','wav'
                    ),
                    'id'=>'form_postfile',
                    'label'=>'Post File',
                    'data_class'=> PageEntity::class,
                    'required'=>false,
                    'attr'=>array(
                        'class' => 'form-control',
                        'name' => 'postfile',
                    )
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $addMenu = $form->get('addmenu')->getData();
            $menuparent = $form->get('menuparent')->getData();
            if($addMenu == true)
            {
                $menuItem = new \App\Entity\MenuItemEntity();
                $menuItem->setTitle($form->get('title')->getData());
                $menuItem->setUrl('/page/'.$this->clean($form->get('title')->getData()));
                if($menuparent != 0)
                {
                    $menuparent = $em->getRepository(\App\Entity\MenuItemEntity::class)->find($form->get('menuparent')->getData());
                    $menuItem->setParent($menuparent);
                }
                $menuItem->setSystem(true);
                $menuItem->setMenu($menu);
                $em->persist($menuItem);
                $em->flush();
            }
            //get the layout through id
            $layout = $em->getRepository(LayoutEntity::class)->find($form->get('layout')->getData());
            $page = new PageEntity();
            $pageFiles = $request->get('postfiles');
            if(is_array($pageFiles))
            {
                foreach($pageFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                    $page->addFile($fileEntity);
                    //enable the file!
                    $fileEntity->setEnabled(true);
                    $em->persist($fileEntity);
                    $em->flush();
                }
            }
            
            $page->setTitle($form->get('title')->getData());
            $page->setContent($form->get('content')->getData());
            $page->setLayout($layout);
            //set keyword entities
            //first add any new keywords
            $keywordData = $form->get('metakeywords')->getData();
            $keywordHelper->addNewKeywords($keywordData);
            
            //get all the keyword entities based on input
            $keywords = $keywordHelper->getKeywordsByTitle($keywordData);
            //loop through the keywords and add them
            foreach($keywords as $keyword)
            {
                $page->addKeyword($keyword);
            }
            $page->setUrl($this->clean($page->getTitle()));
            $page->setMetadescription($form->get('metadescription')->getData());
            $page->setShowsubpages($form->get('showsubpages')->getData());
            if($addMenu== true)
            {
                $page->setMenu($menuItem);
                $page->setHasmenu(true);
                
                $em->persist($page);
                $em->flush();
                //is there a page connected to the parent menu? if so then it is a parent page and this page needs to be added to it
                $parentPage = $em->getRepository(PageEntity::class)->findBy(array('menu'=>$menuparent));
                if( is_array($parentPage))
                {
                    foreach($parentPage as $pp)
                    {
                        $pp->addPage($page);
                        $em->persist($pp);
                        $em->flush();
                    }
                }
                
            }
            else
            {
                $page->setMenu (null);
                $em->persist($page);
                $em->flush();
            }
            
            $this->addFlash('success', 'Page: '. $page->getTitle() . ' was created successfully!');
            return $this->redirectToRoute('page', array('url'=>$page->getUrl()));
        }
        return $this->render('page/add-page.html.twig', array(
            'form'=>$form->createView()
        ));
    }
    
    private function clean(string $string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
    
    /**
     * @Route("/admin/pages/{url}/edit", name="editPage")
     */
    public function editPage(string $url, Request $request,Breadcrumbs $breadcrumbs, PrettyPhoto $prettyPhoto, FileUploaderUtility $fileUploaderUtil, HelperMethods $helper, KeywordHelper $keywordHelper)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('edit_page', null);
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(PageEntity::class)->findBy(array('url'=>$url));
        $page = $page[0];
        if(!$page)
            return $this->redirectToRoute('app_home_home');
        //set breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Pages', '/admin/pages');
        $breadcrumbs->setActive('Edit Page: '.$page->getTitle());
        $breadcrumbs->setBreadcrumbs();
        
        //get available layouts
        $layouts = $em->getRepository(LayoutEntity::class)->findAll();
        $choices = array();
        foreach($layouts as $layout)
        {
            $choices[$layout->getType()] = $layout->getId();
        }
        $siteMenu = $em->getRepository(\App\Entity\MenuEntity::class)->find(7);
        $mItems = $siteMenu->getItems();
        $menuArray = array();
        $menuArray['None'] = 0;
        
        $hasMenu = 0;
        $menuId = 0;
        $pageMenuId = 0;
        if($page->getMenu() != null)
        {
            $hasMenu = 1;
            $pageMenuId = $page->getMenu()->getId();
            if($page->getMenu()->getParent() != null)
                $menuId = $page->getMenu()->getParent()->getId();
            else
                $menuId = 0;
        }
        
        foreach($mItems as $item)
        {
            if($item->getParent() == null && $item->getId() != $pageMenuId)
            {
                $menuArray[$item->getTitle()] = $item->getId();
                if($item->getChildrencount() > 0)
                {
                    foreach($item->getChildren() as $child)
                    {
                        $menuArray = $helper->buildRecursiveMenuArray($child, $menuArray, 1,$pageMenuId);
                    }
                }
            }
        }
        
        $files = $page->getFiles();
        $existingFiles = $fileUploaderUtil->makeExistingFilesArray($files, $this->getParameter('upload_directory'));
        //create form
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'data' => $page->getTitle(),
                    'attr' => array(
                        'placeholder' => 'Page Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('content', CKEditorType::class, array(
                    'data' => $page->getContent(),
                    'attr' => array(
                        'placeholder' => 'Page Content',
                        'class' => 'form-control',
                        'name' => 'content'
                    ),
                    'id'=>'form_content',
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                ))
                ->add('addmenu', ChoiceType::class, array(
                    'data'=>$hasMenu,
                    'choices'=>array('Yes'=>1,'No'=>0),
                    'attr' => array(
                        'class'=>'form-control',
                        'name'=>'addmenu'
                    )
                ))
                ->add('menuparent', ChoiceType::class, array(
                    'data'=>$menuId,
                    'choices'=>$menuArray,
                    'attr'=>array(
                        'class'=>'form-control',
                        'name'=>'menuparent'
                    )
                ))
                ->add('showsubpages', ChoiceType::class, array(
                    'data'=>$page->getShowsubpages(),
                    'choices'=>array(
                        'No'=>0,
                        'Yes'=>1
                    ),
                    'attr'=>array(
                        'class'=>'form-control',
                        'name'=>'showsubpages'
                    )
                ))
                ->add('metakeywords', AutoTagsType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'dataUrl'=>'/ajax/keywords',
                    'id'=>'metakeywords',
                    'attr' => array(
                        'placeholder' => 'Keywords',
                        'class' => 'form-control',
                        'name' => 'metakeywords'
                    ),
                    'data'=>$keywordHelper->makeKeywordString($page->getKeywords())
                ))
                ->add('metadescription', TextareaType::class, array(
                    'data'=>$page->getMetadescription(),
                    'attr' => array(
                        'placeholder'=>'Meta Description',
                        'class'=>'form-control',
                        'name'=>'metadescription'
                    )
                ))
                ->add('layout', ChoiceType::class, array(
                    'data'=>$page->getLayout()->getId(),
                    'choices'=> $choices,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'layout'
                    )
                ))
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'PrettyPhoto'=>$prettyPhoto,
                    'existingFiles'=>$existingFiles,
                    'uploadUrl'=>'/file/upload/page/test/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'fileTypes'=>array(
                        'png','jpg','jpeg','gif','pdf','docx','mp3','wav'
                    ),
                    'id'=>'form_postfile',
                    'label'=>'Post File',
                    'data_class'=> PageEntity::class,
                    'required'=>false,
                    'attr'=>array(
                        'class' => 'form-control',
                        'name' => 'postfile',
                    )
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $addMenu = $form->get('addmenu')->getData();
            if($addMenu == true)
            {
                $pageMenu = $page->getMenu();
                if($form->get('menuparent')->getData() != 0)
                {
                    $parent = $em->getRepository(\App\Entity\MenuItemEntity::class)->find($form->get('menuparent')->getData());
                    $pageMenu->setParent($parent);
                }

                $em->persist($pageMenu);
                $em->flush();
                $page->setMenu($pageMenu);
                $page->setHasmenu(true);
                $parentPage = $em->getRepository(PageEntity::class)->findBy(array('menu'=>$form->get('menuparent')->getData()));
                if( is_array($parentPage))
                {
                    foreach($parentPage as $pp)
                    {
                        //make sure parent page does not already have this page
                        if(!$pp->hasPage($page))
                        {
                            $pp->addPage($page);
                            $em->persist($pp);
                            $em->flush();
                        }
                    }
                }
            }
            //get the layout through id
            $layout = $em->getRepository(LayoutEntity::class)->find($form->get('layout')->getData());
            foreach($files as $file)
            {
                //remove all files connected to post
                $page->removeFile($file);
                
            }
            $postFiles = $request->get('postfiles');
            if(is_array($postFiles))
            {
                foreach($postFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                    {
                        $page->addFile($fileEntity);
                        //enable the file!
                        $fileEntity->setEnabled(true);
                        $em->persist($fileEntity);
                        $em->flush();
                    }
                }
            }
            $page->setTitle($form->get('title')->getData());
            $page->setContent($form->get('content')->getData());
            $page->setLayout($layout);
            $page->setUrl($this->clean($page->getTitle()));
            //set keyword entities
            //first add any new keywords
            $keywordData = $form->get('metakeywords')->getData();
            $keywordHelper->addNewKeywords($keywordData);
            
            //get all the keyword entities based on input
            $keywords = $keywordHelper->getKeywordsByTitle($keywordData);
            //reset projects keywords
            $page->resetKeywords();
            //loop through the keywords and add them
            foreach($keywords as $keyword)
            {
                $page->addKeyword($keyword);
            }
            $page->setMetadescription($form->get('metadescription')->getData());
            $page->setShowsubpages($form->get('showsubpages')->getData());
            
            $em->persist($page);
            $em->flush();
            $this->addFlash('success', 'Page: '. $page->getTitle() . ' was edited successfully!');
            return $this->redirectToRoute('page', array('url'=>$page->getUrl()));
        }
        return $this->render('page/edit-page.html.twig', array(
            'form'=>$form->createView(),
            'page'=>$page
        ));
    }
    
    /**
     * @Route("/admin/pages/{url}/delete", name="deletePage")
     */
    public function deletePage(string $url, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('delete_page', null);
        $page = $this->getDoctrine()->getManager()->getRepository(PageEntity::class)->findBy(array('url'=>$url));
        $page = $page[0];
        if(!$page)
            return $this->redirectToRoute('app_home_home');
        //set breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->addBreadcrumb('Pages', '/admin/pages');
        $breadcrumbs->setActive('Delete Page: '.$page->getTitle());
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('hidden', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array(
                    
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            //remove page menu if there is one
            if($page->getMenu() != null)
            {
                $menu = $page->getMenu();
                $page->setMenu(null);
                $em->persist($page);
                $em->flush();
                $em->remove($menu);
                $em->flush();
            }
            //we need to remove all files associated with this page as well
            $files = $page->getFiles();
            foreach($files as $file)
            {
                $page->removeFile($file);
                $em->persist($page);
                $em->flush();
                $file->removeFile($this->getParameter('upload_directory'));
                $em->remove($file);
                $em->flush();
            }
            $em->remove($page);
            $em->flush();
            $this->addFlash('success','Page: '. $page->getTitle() . ' was deleted successfully!');
            return $this->redirectToRoute('viewPages');
        }
        
        return $this->render('page/delete-page.html.twig', array(
            'page' => $page,
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Route("/page/{url}", name="page")
     */
    public function index(string $url,Breadcrumbs $breadcrumbs, Fotorama $fotoRama)
    {
        parent::hideProfiler($this->getUser());
        $page = $this->getDoctrine()->getManager()->getRepository(PageEntity::class)->findBy(array('url'=>$url));
        if(!$page)
            return $this->redirectToRoute('app_home_home');
        $page = $page[0];
        //set breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->setActive($page->getTitle());
        $breadcrumbs->setBreadcrumbs();
        // replace this line with your own code!
        $slideshow = null;
        $files = $page->getFiles();
        $imageArray = null;
        if($files instanceof PersistentCollection)
        {
            $imageArray = array();
            foreach($files as $file)
            {
                if($file->getType() == 'jpg' || $file->getType() == 'png')
                    $imageArray[] = $file;
            }
        }
        if(is_array($imageArray))
        {
            $fotoRama->addFiles($imageArray);
            $fotoRama->lightbox = false;
            $fotoRama->setId('blogpost');
            $slideshow = $fotoRama->createView();
        }
        
        return $this->render('page/index.html.twig', array(
            'page'=>$page,
            'slideshow'=>$slideshow
        ));
    }
}
