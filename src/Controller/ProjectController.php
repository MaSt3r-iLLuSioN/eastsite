<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\PersistentCollection;

use App\Entity\Project;
use App\Entity\Config;
use App\Service\Breadcrumbs;
use App\Service\Fotorama;
use App\Service\PrettyPhoto;
use App\Service\FileUploaderUtility;
use App\Service\Filterizer;
use App\Service\Liker;
use App\Service\HereMapper;
use App\Service\KeywordHelper;
use App\Entity\CategoryGroup;
use App\Entity\CategoryEntity;
use App\Entity\FileEntity;
use App\Form\Type\JQueryFileUploaderType;
use App\Form\Type\CKEditorType;
use App\Form\Type\AutoTagsType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


class ProjectController extends BaseController
{
    /**
     * @Route("/admin/projects/add", name="addProject")
     */
    public function createProject(Breadcrumbs $breadcrumbs, PrettyPhoto $prettyPhoto, Request $request, HereMapper $hereMapper, KeywordHelper $keywordHelper)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('add_project', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Projects', '/admin/projects');
        $breadcrumbs->setActive('Add Project');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $config = $em->getRepository(Config::class)->find(1);
        $categories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$config->getProjectcategory()]);
        $choices = array();
        $project = new Project();
        foreach($categories as $category)
        {
            $choices[$category->getTitle()] = $category->getId();
        }
        $statuses = array();
        $statuses['In Progress'] = 1;
        $statuses['Completed'] = 2;
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Project Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('location', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Project Location',
                        'class' => 'form-control',
                        'name' => 'location'
                    )
                ))
                ->add('style', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Project Style',
                        'class' => 'form-control',
                        'name' => 'style'
                    )
                ))
                ->add('content', CKEditorType::class, array(
                    'attr' => array(
                        'placeholder' => 'Project Content',
                        'class' => 'form-control',
                        'name' => 'content'
                    ),
                    'id'=>'form_content',
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
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
                        'placeholder' => 'Project Meta Description',
                        'class' => 'form-control',
                        'name' => 'metadescription'
                    )
                ))
                ->add('category', ChoiceType::class, array(
                    'choices'=> $choices,
                    'attr' => array(
                        'class' => 'form-control contact-control',
                        'name' => 'category'
                    )
                ))
                ->add('status', ChoiceType::class, array(
                    'choices'=>$statuses,
                    'attr'=>array(
                        'class'=>'form-control contact-control',
                        'name'=>'status'
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
            $project = new Project();
            $project->setTitle($form->get('title')->getData());
            $title = $project->getTitle();
            $title = str_replace(' ', '-', strtolower($title));
            $project->setSystemtitle($title);
            //set keyword entities
            //first add any new keywords
            $keywordData = $form->get('metakeywords')->getData();
            $keywordHelper->addNewKeywords($keywordData);
            
            //get all the keyword entities based on input
            $keywords = $keywordHelper->getKeywordsByTitle($keywordData);
            //loop through the keywords and add them
            foreach($keywords as $keyword)
            {
                $project->addKeyword($keyword);
            }
            $project->setMetadescription($form->get('metadescription')->getData());
            $project->setAuthor($this->getUser());
            $project->setContent($form->get('content')->getData());
            $project->setDatetime(new \DateTime());
            $project->setRevision();
            $project->setType('project');
            $project->setStatus($form->get('status')->getData());
            $project->setLocation($form->get('location')->getData());
            $geoCords = $hereMapper->getGeoCords($project->getLocation());
            $project->setLatitude($geoCords['lat']);
            $project->setLongitude($geoCords['long']);
            $project->setStyle($form->get('style')->getData());
            $category = $em->getRepository(CategoryEntity::class)->findBy(['id'=>$form->get('category')->getData()]);
            $project->setCategory($category[0]);
            //get uploaded file name(s)
            //$file = $form->get('image')->getData();
            

            //$post->setImage($fileName);
            $em->persist($project);
            $em->flush();
            $this->addFlash('success', 'Project "' . $project->getTitle() . '" was created successfully!');
                return $this->redirectToRoute('adminProjects');
        }
        return $this->render('project/add_project.html.twig',array(
            'form'=>$form->createView()
        ));
    }
    
    /**
     * @Route("/admin/projects", name="adminProjects")
     */
    public function adminProjects(Breadcrumbs $breadcrumbs)
    {  
        $this->denyAccessUnlessGranted('admin_projects', null);
        parent::hideProfiler($this->getUser());
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('All Site Projects');
        $breadcrumbs->setBreadcrumbs();
        $em = $this->getDoctrine()->getManager();
        $projects = $em->getRepository(Project::class)->findAll();
        return $this->render('project/admin_project.html.twig', array(
            'projects'=>$projects
        ));
    }
    
    /**
     * @Route("/admin/projects/{project}/edit", name="editProject")
     * @param Project $project Project is the entity for project type content
     */
    public function editProject(Project $project, Breadcrumbs $breadcrumbs, FileUploaderUtility $fileUploaderUtil, PrettyPhoto $prettyPhoto, Request $request, HereMapper $hereMapper, KeywordHelper $keywordHelper)
    {  
        $this->denyAccessUnlessGranted('edit_project', null);
        parent::hideProfiler($this->getUser());
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Projects', '/admin/projects');
        $breadcrumbs->setActive('Edit Project');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        
        $config = $em->getRepository(Config::class)->find(1);
        $categories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$config->getProjectcategory()]);
        
        
        $files = $project->getFiles();
        $existingFiles = $fileUploaderUtil->makeExistingFilesArray($files, $this->getParameter('upload_directory'));
        
        $choices = array();
        foreach($categories as $category)
        {
            $choices[$category->getTitle()] = $category->getId();
        }
        $statuses = array();
        $statuses['In Progress'] = 1;
        $statuses['Completed'] = 2;
        $categoryValues = array();
        $category = $project->getCategory();
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Project Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    ),
                    'data'=>$project->getTitle()
                ))
                ->add('style', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Project Style',
                        'class' => 'form-control',
                        'name' => 'style'
                    ),
                    'data'=>$project->getStyle()
                ))
                ->add('location', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Project Location',
                        'class' => 'form-control',
                        'name' => 'location'
                    ),
                    'data'=>$project->getLocation()
                ))
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'uploadUrl'=>'/file/upload/project/projects-'.$project->getId().'/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'id'=>'form_postfile',
                    'label'=>'Project File',
                    'data_class'=> Project::class,
                    'required'=>false,
                    'fileTypes'=>array(
                        'png','jpg','jpeg','gif','pdf','docx','mp3','wav'
                    ),
                    'PrettyPhoto'=>$prettyPhoto,
                    'existingFiles'=>$existingFiles,
                    'attr'=>array(
                        'class' => 'form-control',
                        'name' => 'postfile'
                    )
                ))
                ->add('content', CKEditorType::class, array(
                    'attr' => array(
                        'placeholder' => 'Project Content',
                        'class' => 'form-control',
                        'name' => 'content'
                    ),
                    'id'=>'form_content',
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'data'=>$project->getContent()
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
                    'data'=>$keywordHelper->makeKeywordString($project->getKeywords())
                ))
                ->add('metadescription', TextareaType::class, array(
                    'attr' => array(
                        'placeholder' => 'Project Meta Description',
                        'class' => 'form-control',
                        'name' => 'metadescription'
                    ),
                    'data'=>$project->getMetadescription()
                ))
                ->add('status', ChoiceType::class, array(
                    'choices'=>$statuses,
                    'attr'=>array(
                        'class'=>'form-control contact-control',
                        'name'=>'status'
                    ),
                    'data'=>$project->getStatus()
                ))
                ->add('category', ChoiceType::class, array(
                    'choices'=> $choices,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'category'
                    ),
                    'data'=>$category->getId()
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            foreach($files as $file)
            {
                //remove all files connected to post
                $project->removeFile($file);
                
            }
            
            $postFiles = $request->get('postfiles');
            if(is_array($postFiles))
            {
                foreach($postFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                        $project->addFile($fileEntity);
                }
            }
            
            $project->setTitle($form->get('title')->getData());
            $title = $project->getTitle();
            $title = str_replace(' ', '-', strtolower($title));
            $project->setSystemtitle($title);
            //set keyword entities
            //first add any new keywords
            $keywordData = $form->get('metakeywords')->getData();
            $keywordHelper->addNewKeywords($keywordData);
            
            //get all the keyword entities based on input
            $keywords = $keywordHelper->getKeywordsByTitle($keywordData);
            //reset projects keywords
            $project->resetKeywords();
            //loop through the keywords and add them
            foreach($keywords as $keyword)
            {
                $project->addKeyword($keyword);
            }
            $project->setMetadescription($form->get('metadescription')->getData());
            $project->setContent($form->get('content')->getData());
            $project->setStatus($form->get('status')->getData());
            $category = $em->getRepository(CategoryEntity::class)->findBy(['id'=>$form->get('category')->getData()]);
            $project->setCategory($category[0]);
            $location = $form->get('location')->getData();
            if($project->getLocation() != $location)
            {
                $project->setLocation($form->get('location')->getData());
                $geoCords = $hereMapper->getGeoCords($project->getLocation());
                $project->setLatitude($geoCords['lat']);
                $project->setLongitude($geoCords['long']);
            }
            $project->setStyle($form->get('style')->getData());
            $em->persist($project);
            $em->flush();
            $this->addFlash('success', 'Project "' . $project->getTitle() . '" was edited successfully!');
                return $this->redirectToRoute('adminProjects');
        }
        return $this->render('project/edit_project.html.twig', array(
            'form'=>$form->createView(),
            'project'=>$project
        ));
    }
    
    /**
     * @Route("/admin/projects/{project}/delete", name="deleteProject")
     */
    public function deleteProject(Project $project, Breadcrumbs $breadcrumbs, Request $request)
    {  
        $this->denyAccessUnlessGranted('delete_project', null);
        parent::hideProfiler($this->getUser());
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Projects', '/admin/projects');
        $breadcrumbs->setActive('Delete Project');
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
            //we need to remove all files associated with this project as well
            $files = $project->getFiles();
            foreach($files as $file)
            {
                $project->removeFile($file);
                $em->persist($project);
                $em->flush();
                $file->removeFile($this->getParameter('upload_directory'));
                $em->remove($file);
                $em->flush();
            }
            $em->remove($project);
            $em->flush();
            $this->addFlash('success', 'Project "'.$project->getTitle() . '" was deleted successfully!');
            return $this->redirectToRoute('adminProjects');
        }
        
        return $this->render('project/delete_project.html.twig', array(
            'form'=>$form->createView(),
            'project'=>$project
        ));
    }
    
    //view projects by category or actual project
    /**
     * @Route("/projects/{path}", name="viewProject", requirements={"path"=".+"})
     */
    public function viewProject(string $path, Request $request, Breadcrumbs $breadcrumbs, Fotorama $fotoRama, Filterizer $filterizer, Liker $liker, HereMapper $mapper)
    {
        parent::hideProfiler($this->getUser());
        $segments = explode('/', $path);
        $segmentCount = count($segments);
        //the last segment is either going to be a project title or a category title
        $em = $this->getDoctrine()->getManager();
        //first lets see if the last segment is a project
        $project = $em->getRepository(Project::class)->findBy(['systemtitle'=>$segments[$segmentCount-1]]);
        if(is_array($project) && isset($project[0]) && $project[0] instanceof Project)
        {
            //the last segment is a Project!
            $projectEntity = $project[0];
            //get the projects category
            $category = $projectEntity->getCategory();
            //build the breadcrumbs
            $breadcrumbs->addBreadcrumb('Home', '/');
            $breadcrumbs->addBreadcrumb('Projects', '/projects');
            $category->handleBreadcrumbs($breadcrumbs,'projects', $category);
            $breadcrumbs->setActive($projectEntity->getTitle());
            $breadcrumbs->setBreadcrumbs();
            
            //manage the files
            $files = $projectEntity->getFiles();
            $slideshow = '';
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
                $fotoRama->setId('project');
                $slideshow = $fotoRama->createView();
            }
            $liker->setNode($projectEntity);

            //add http caching
            $response = $this->render('project/view_project.html.twig', array(
                'project' => $projectEntity,
                'category' => $category,
                'slideshow' => $slideshow,
                'liker'=>$liker->createView()
            ));
            
            $response->setCache(array(
                'etag'          => md5($response->getContent()),
                'last_modified' => $projectEntity->getRevision(),
                'max_age'       => 10,
                's_maxage'      => 10,
                'public'        => true,
                // 'private'    => true,
            ));
            
            $response->isNotModified($request);
            
            return $response;
            
        }
        else
        {
            //check to see if the last segment is a category then.
            $categoryGroup = $em->getRepository(CategoryGroup::class)->findBy(['title'=>'Project Group']);
            $categories = $categoryGroup[0]->getCategories();
            $foundCat = false;
            $fotoRama->nav = 'false';
            
            foreach($categories as $category)
            {
                if($category->getSystemtitle() == $segments[$segmentCount-1])
                {
                    //the category has been found!
                    //breadcrumbs
                    $breadcrumbs->addBreadcrumb('Home', '/');
                    $breadcrumbs->addBreadcrumb('Projects', '/projects');
                    if($category->getParent() != null)
                    {
                        $category->handleBreadcrumbs($breadcrumbs,'projects', $category);
                    }
                    $breadcrumbs->setActive($category->getTitle());
                    $breadcrumbs->setBreadcrumbs();
                    
                    $filterizer->setRepo(Project::class);
                    $filterizer->setListTemplate('project/project-list-view.html.twig');
                    $filterizer->setGridTemplate('project/project-grid-view.html.twig');
                    $filterizer->setTableStartTemplate('project/project-table-start.html.twig');
                    $filterizer->setTableTemplate('project/project-table-view.html.twig');
                    $filterizer->setTableEndTemplate('project/project-table-end.html.twig');
                    $filterizer->setPaginationUrl('/projects/'.$category->getGenerateurl() . '?');
                    $filterizer->addSearchField('searchTitle', 'Search By Title', 'title');
                    $filterizer->addWhere('category', $category->getId(), '=');
                    $filterizer->setUseLiker(true);
                    $filterizer->setLiker($liker);
                    $filterizer->handleRequest($request);
                    $filterizer->setContent();
                    $data = $filterizer->getData();
                    $projects = $em->getRepository(Project::class)->findBy(['category'=>$category->getId()]);
                    $mapper->setNode($projects);
                    $childrenCategories = $category->getChildren();
                    $fotoRama->setId('project');
                    $fotoRama->setHeight('50%');
                    $fotoRama->initFotorama();
                    return $this->render('project/view_categories.html.twig', array(
                        'childCategories'=>$childrenCategories,
                        'category'=>$category,
                        'filterizer'=>$filterizer->createView(),
                        'projects'=>$projects,
                        'mapper'=>$mapper->createView()
                    ));
                }
            }
            //no category and no posts? This page should not be displayed!!!
            return $this->redirectToRoute('viewAllBlogPost');
            
        }
    }
    
    /**
     * view all projects
     * @Route("/projects", name="viewProjects")
     */
    public function viewProjects(Breadcrumbs $breadcrumbs, Fotorama $fotoRama, Filterizer $filterizer, Liker $liker, HereMapper $mapper, Request $request)
    {
        parent::hideProfiler($this->getUser());
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->setActive('Projects');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $categoryGroup = $em->getRepository(CategoryGroup::class)->findBy(['title'=>'Project Group']);
        $categories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$categoryGroup[0], 'parent'=>null],['title'=>'ASC']);
        $filterizer->setRepo(Project::class);
        $filterizer->setListTemplate('project/project-list-view.html.twig');
        $filterizer->setGridTemplate('project/project-grid-view.html.twig');
        $filterizer->setTableStartTemplate('project/project-table-start.html.twig');
        $filterizer->setTableTemplate('project/project-table-view.html.twig');
        $filterizer->setTableEndTemplate('project/project-table-end.html.twig');
        $filterizer->setPaginationUrl('/projects?');
        $filterizer->addSearchField('searchTitle', 'Search By Title', 'title');
        $filterizer->setUseLiker(true);
        $filterizer->setLiker($liker);
        $filterizer->handleRequest($request);
        $filterizer->setContent();
        //$data = $filterizer->getData();
        
        $fotoRama->nav = 'false';
        $fotoRama->setId('project');
        $fotoRama->setHeight('50%');
        $fotoRama->initFotorama();
        $projects = $em->getRepository(Project::class)->findAll();
        $mapper->setNode($projects);
        return $this->render('project/view_projects.html.twig', array(
            'categories'=>$categories,
            'projects'=>$projects,
            //'mapper'=>$mapper,
            'filterizer'=>$filterizer->createView(),
            'mapper'=>$mapper->createView()
        ));
    }
}
