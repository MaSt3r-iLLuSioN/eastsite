<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use App\Service\Breadcrumbs;
use App\Service\PrettyPhoto;
use App\Service\FileUploaderUtility;
use App\Service\KeywordHelper;
use App\Entity\CategoryGroup;
use App\Entity\Config;
use App\Entity\FileEntity;
use App\Form\Type\JQueryFileUploaderType;
use App\Form\Type\AutoTagsType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ConfigController extends BaseController
{
    /**
     * @Route("/admin/config", name="siteConfig")
     */
    public function siteConfig(Breadcrumbs $breadcrumbs, Request $request, PrettyPhoto $prettyPhoto, FileUploaderUtility $fileUploaderUtil, KeywordHelper $keywordHelper)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Site Configuration');
        $breadcrumbs->setBreadcrumbs();
        $em = $this->getDoctrine()->getManager();
        $choices = array();
        $categoryGroups = $em->getRepository(CategoryGroup::class)->findAll();
        $config = $em->getRepository(Config::class)->find(1);
        foreach($categoryGroups as $cg)
        {
            $choices[$cg->getTitle()] = $cg->getId();
        }
        $projectCategory = $config->getProjectcategory();
        $blogCategory = $config->getBlogcategory();
        
        $files = $config->getFiles();
        $existingFiles = $fileUploaderUtil->makeExistingFilesArray($files, $this->getParameter('upload_directory'));
        //setp form
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Site Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    ),
                    'data'=>$config->getTitle()
                ))
                ->add('facebooklink', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Site Facebook Link',
                        'class' => 'form-control',
                        'name' => 'facebooklink'
                    ),
                    'required'=>false,
                    'data'=>$config->getFacebooklink()
                ))
                ->add('googlelink', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Site Google Link',
                        'class' => 'form-control',
                        'name' => 'googlelink'
                    ),
                    'required'=>false,
                    'data'=>$config->getGooglelink()
                ))
                ->add('twitterlink', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Site Twitter Link',
                        'class' => 'form-control',
                        'name' => 'twitterlink'
                    ),
                    'required'=>false,
                    'data'=>$config->getTwitterlink()
                ))
                ->add('instagramlink', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Site Instagram Link',
                        'class' => 'form-control',
                        'name' => 'instagramlink'
                    ),
                    'required'=>false,
                    'data'=>$config->getInstagramlink()
                ))
                ->add('youtubelink', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Site Youtube Link',
                        'class' => 'form-control',
                        'name' => 'youtubelink'
                    ),
                    'required'=>false,
                    'data'=>$config->getYoutubelink()
                ))
                ->add('linkedinlink', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Site LinkedIn Link',
                        'class' => 'form-control',
                        'name' => 'linkedinlink'
                    ),
                    'required'=>false,
                    'data'=>$config->getLinkedinlink()
                ))
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'uploadUrl'=>'/file/upload/config/config/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'id'=>'form_postfile',
                    'label'=>'Project File',
                    'data_class'=> Config::class,
                    'required'=>false,
                    'fileTypes'=>array(
                        'png','jpg','jpeg','gif','pdf','docx','mp3','wav'
                    ),
                    'PrettyPhoto'=>$prettyPhoto,
                    'maxNumberOfFiles'=>1,
                    'existingFiles'=>$existingFiles,
                    'attr'=>array(
                        'class' => 'form-control',
                        'name' => 'postfile'
                    )
                ))->add('metakeywords', AutoTagsType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'dataUrl'=>'/ajax/keywords',
                    'id'=>'metakeywords',
                    'attr' => array(
                        'placeholder' => 'Keywords',
                        'class' => 'form-control',
                        'name' => 'metakeywords'
                    ),
                    'data'=>$keywordHelper->makeKeywordString($config->getKeywords())
                ))
                ->add('metadescription', TextareaType::class, array(
                    'attr' => array(
                        'placeholder' => 'Project Meta Description',
                        'class' => 'form-control',
                        'name' => 'metadescription'
                    ),
                    'data'=>$config->getMetadescription()
                ))->add('projectcategory', ChoiceType::class, array(
                    'choices'=> $choices,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'category'
                    ),
                    'data'=>$projectCategory->getId()
                ))->add('blogcategory', ChoiceType::class, array(
                    'choices'=> $choices,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'category'
                    ),
                    'data'=>$blogCategory->getId()
                ))->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            foreach($files as $file)
            {
                //remove all files connected to post
                $config->removeFile($file);
                
            }
            
            $postFiles = $request->get('postfiles');
            if(is_array($postFiles))
            {
                foreach($postFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                        $config->addFile($fileEntity);
                }
            }
            $title = $form->get('title')->getData();
            $projectCatId = $form->get('projectcategory')->getData();
            $blogCatId = $form->get('blogcategory')->getData();
            $keywordData = $form->get('metakeywords')->getData();
            $metaDescription = $form->get('metadescription')->getData();
            $googleLink = $form->get('googlelink')->getData();
            $facebookLink= $form->get('facebooklink')->getData();
            $twitterLink = $form->get('twitterlink')->getData();
            $instagramLink = $form->get('instagramlink')->getData();
            $youtubeLink = $form->get('youtubelink')->getData();
            $linkedinLink = $form->get('linkedinlink')->getData();
            
            $projectCat = $em->getRepository(CategoryGroup::class)->find($projectCatId);
            $blogCat = $em->getRepository(CategoryGroup::class)->find($blogCatId);
            
            $config->setTitle($title);
            $config->setFacebooklink($facebookLink);
            $config->setGooglelink($googleLink);
            $config->setLinkedinlink($linkedinLink);
            $config->setYoutubelink($youtubeLink);
            $config->setInstagramlink($instagramLink);
            $config->setTwitterlink($twitterLink);
            
            $config->setProjectcategory($projectCat);
            $config->setBlogcategory($blogCat);
            $config->setMetadescription($metaDescription);
            //set keyword entities
            //first add any new keywords
            $keywordHelper->addNewKeywords($keywordData);
            
            //get all the keyword entities based on input
            $keywords = $keywordHelper->getKeywordsByTitle($keywordData);
            //reset projects keywords
            $config->resetKeywords();
            //loop through the keywords and add them
            foreach($keywords as $keyword)
            {
                $config->addKeyword($keyword);
            }
            $em->persist($config);
            $em->flush();
            $this->addFlash('success', 'Site Configuration saved successfully!');
            return $this->redirectToRoute('siteConfig');
        }
        return $this->render('config/site-config.html.twig', array(
            'form'=>$form->createView()
        ));
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
