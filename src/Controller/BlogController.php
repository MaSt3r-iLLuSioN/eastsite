<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use App\Service\Breadcrumbs;
use App\Service\Fotorama;
use App\Service\PrettyPhoto;
use App\Service\FileUploaderUtility;
use App\Service\Filterizer;
use App\Service\Liker;
use App\Service\Commentor;
use App\Service\KeywordHelper;
use App\Entity\Blog;
use App\Entity\Config;
use App\Entity\CategoryGroup;
use App\Entity\CategoryEntity;
use App\Entity\FileEntity;
use App\Form\Type\JQueryFileUploaderType;
use App\Form\Type\CKEditorType;
use App\Form\Type\AutoTagsType;

use Doctrine\ORM\PersistentCollection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class BlogController extends BaseController
{
    /**
     * @Route("/admin/blog", name="adminBlog")
     */
    public function adminBlog(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('view_admin_blog_area', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashboard', '/admin');
        $breadcrumbs->setActive('Blog', '/admin/blog');
        $breadcrumbs->setBreadcrumbs();
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository(Blog::class)->findAll();
        return $this->render('blog/admin_post.html.twig', array(
            'posts'=>$posts
        ));
    }
    /**
     * @Route("/admin/blog/add", name="addBlogPost")
     */
    public function addPost(Breadcrumbs $breadcrumbs, PrettyPhoto $prettyPhoto, Request $request, KeywordHelper $keywordHelper)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('add_blog_post', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Blog', '/admin/blog');
        $breadcrumbs->setActive('Add Post', '/admin/blog/add');
        $breadcrumbs->setBreadcrumbs();
        $em = $this->getDoctrine()->getManager();
        
        $config = $em->getRepository(Config::class)->find(1);
        $categories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$config->getBlogcategory()]);
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
                        'placeholder' => 'Post Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('content', CKEditorType::class, array(
                    'attr' => array(
                        'placeholder' => 'Post Content',
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
                        'placeholder' => 'Post Meta Description',
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
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'PrettyPhoto'=>$prettyPhoto,
                    'uploadUrl'=>'/file/upload/blog/test/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'fileTypes'=>array(
                        'png','jpg','jpeg','gif','pdf','docx','mp3','wav'
                    ),
                    'id'=>'form_postfile',
                    'label'=>'Post File',
                    'data_class'=> Blog::class,
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
            $post = new Blog();
            $postFiles = $request->get('postfiles');
            if(is_array($postFiles))
            {
                foreach($postFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                    $post->addFile($fileEntity);
                }
            }
            
            $post->setTitle($form->get('title')->getData());
            $title = $post->getTitle();
            $title = str_replace(' ', '-', strtolower($title));
            $post->setSystemtitle($title);
            //set keyword entities
            //first add any new keywords
            $keywordData = $form->get('metakeywords')->getData();
            $keywordHelper->addNewKeywords($keywordData);
            
            //get all the keyword entities based on input
            $keywords = $keywordHelper->getKeywordsByTitle($keywordData);
            //loop through the keywords and add them
            foreach($keywords as $keyword)
            {
                $post->addKeyword($keyword);
            }
            $post->setMetadescription($form->get('metadescription')->getData());
            $post->setAuthor($this->getUser());
            $post->setContent($form->get('content')->getData());
            $post->setDatetime(new \DateTime());
            $post->setType('blog');
            $category = $em->getRepository(CategoryEntity::class)->findBy(['id'=>$form->get('category')->getData()]);
            $post->setCategory($category[0]);
            $em->persist($post);
            $em->flush();
            $this->addFlash('success', 'Blog post "' . $post->getTitle() . '" was created successfully!');
                return $this->redirectToRoute('adminBlog');
        }
        return $this->render('blog/add_post.html.twig',array(
            'form'=>$form->createView()
        ));
    }
    /**
     * @Route("/admin/blog/{blog}/edit", name="editBlogPost")
     */
    public function editPost(Blog $blog, Breadcrumbs $breadcrumbs, FileUploaderUtility $fileUploaderUtil, PrettyPhoto $prettyPhoto, Request $request,KeywordHelper $keywordHelper)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('add_blog_post', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Blog', '/admin/blog');
        $breadcrumbs->addBreadcrumb($blog->getTitle(), '/blog/'.$blog->getId());
        $breadcrumbs->setActive('Edit Post', '/admin/blog/'.$blog->getId().'/edit');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        
        $config = $em->getRepository(Config::class)->find(1);
        $categories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$config->getBlogcategory()]);
        
        $files = $blog->getFiles();
        $existingFiles = $fileUploaderUtil->makeExistingFilesArray($files, $this->getParameter('upload_directory'));
        
        $choices = array();
        foreach($categories as $category)
        {
            $choices[$category->getTitle()] = $category->getId();
        }
        $categoryValues = array();
        $category = $blog->getCategory();
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Post Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    ),
                    'data'=>$blog->getTitle()
                ))
                ->add('postfile', JQueryFileUploaderType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'uploadUrl'=>'/file/upload/blog/test/postfile',
                    'hiddenFieldName' => 'postfiles',
                    'id'=>'form_postfile',
                    'label'=>'Post File',
                    'data_class'=> Blog::class,
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
                        'placeholder' => 'Post Content',
                        'class' => 'form-control',
                        'name' => 'content'
                    ),
                    'id'=>'form_content',
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'data'=>$blog->getContent()
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
                    'data'=>$keywordHelper->makeKeywordString($blog->getKeywords())
                ))
                ->add('metadescription', TextareaType::class, array(
                    'attr' => array(
                        'placeholder' => 'Post Meta Description',
                        'class' => 'form-control',
                        'name' => 'metadescription'
                    ),
                    'data'=>$blog->getMetadescription()
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
                $blog->removeFile($file);
                
            }
            
            $postFiles = $request->get('postfiles');
            if(is_array($postFiles))
            {
                foreach($postFiles as $fileId)
                {
                    $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                    if($fileEntity instanceof FileEntity)
                        $blog->addFile($fileEntity);
                }
            }
            
            $blog->setTitle($form->get('title')->getData());
            $title = $blog->getTitle();
            $title = str_replace(' ', '-', strtolower($title));
            $blog->setSystemtitle($title);
            //set keyword entities
            //first add any new keywords
            $keywordData = $form->get('metakeywords')->getData();
            $keywordHelper->addNewKeywords($keywordData);
            
            //get all the keyword entities based on input
            $keywords = $keywordHelper->getKeywordsByTitle($keywordData);
            //remove keywords from post
            $blog->resetKeywords();
            //loop through the keywords and add them
            foreach($keywords as $keyword)
            {
                $blog->addKeyword($keyword);
            }
            $blog->setMetadescription($form->get('metadescription')->getData());
            $blog->setContent($form->get('content')->getData());
            $category = $em->getRepository(CategoryEntity::class)->findBy(['id'=>$form->get('category')->getData()]);
            $blog->setCategory($category[0]);
            $em->persist($blog);
            $em->flush();
            $this->addFlash('success', 'Blog post "' . $blog->getTitle() . '" was edited successfully!');
                return $this->redirectToRoute('adminBlog');
        }
        return $this->render('blog/edit_post.html.twig', array(
            'form'=>$form->createView(),
            'post'=>$blog
        ));
    }
    /**
     * @Route("/admin/blog/{blog}/delete", name="deleteBlogPost")
     */
    public function deletePost(Blog $blog, Breadcrumbs $breadcrumbs, Request $request)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('add_blog_post', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Blog', '/admin/blog');
        $breadcrumbs->addBreadcrumb($blog->getTitle(), '/blog/'.$blog->getId());
        $breadcrumbs->setActive('Delete Post','/admin/blog/'.$blog->getId().'/delete');
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
            //we need to remove all files associated with this blog as well
            $files = $blog->getFiles();
            foreach($files as $file)
            {
                $blog->removeFile($file);
                $em->persist($blog);
                $em->flush();
                $file->removeFile($this->getParameter('upload_directory'));
                $em->remove($file);
                $em->flush();
            }
            $em->remove($blog);
            $em->flush();
            $this->addFlash('success', 'Blog Post "'.$blog->getTitle() . '" was deleted successfully!');
            return $this->redirectToRoute('adminBlog');
        }
        
        return $this->render('blog/delete_post.html.twig', array(
            'form'=>$form->createView(),
            'post'=>$blog
        ));
    }
    
    
    /**
     * @Route("/blog", name="viewAllBlogPost")
     */
    public function viewAllPost(Breadcrumbs $breadcrumbs, Fotorama $fotoRama, Filterizer $filterizer, Liker $liker, Request $request)
    {
        parent::hideProfiler($this->getUser());
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->setActive('Blog', '/blog');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $categoryGroup = $em->getRepository(CategoryGroup::class)->findBy(['title'=>'Blog Group']);
        $categories = $em->getRepository(CategoryEntity::class)->findBy(['categorygroup'=>$categoryGroup[0], 'parent'=>null],['title'=>'ASC']);
        $filterizer->setRepo(Blog::class);
        $filterizer->setListTemplate('blog/blog-post-list-view.html.twig');
        $filterizer->setGridTemplate('blog/blog-post-grid-view.html.twig');
        $filterizer->setTableStartTemplate('blog/blog-post-table-start.html.twig');
        $filterizer->setTableTemplate('blog/blog-post-table-view.html.twig');
        $filterizer->setTableEndTemplate('blog/blog-post-table-end.html.twig');
        $filterizer->setPaginationUrl('/blog?');
        $filterizer->addSearchField('searchTitle', 'Search By Title', 'title');
        $filterizer->setUseLiker(true);
        $filterizer->setLiker($liker);
        $filterizer->handleRequest($request);
        $filterizer->setContent();
        $data = $filterizer->getData();
        
        $fotoRama->nav = 'false';
        $fotoRama->setId('blogcategory');
        $fotoRama->setHeight('50%');
        $fotoRama->initFotorama();
        $posts = $em->getRepository(Blog::class)->findAll();
        return $this->render('blog/view_all_post.html.twig', array(
            'categories'=>$categories,
            'posts'=>$posts,
            'filterizer'=>$filterizer->createView()
        ));
    }
    
    /**
     * @Route("/blog/{path}", name="viewBlogPost", requirements={"path"=".+"})
     */
    public function viewPost(string $path, Request $request, Breadcrumbs $breadcrumbs, Fotorama $fotoRama, Filterizer $filterizer, Liker $liker, Commentor $commentor)
    {
        parent::hideProfiler($this->getUser());
        $segments = explode('/', $path);
        $segmentCount = count($segments);
        //the last segment is either going to be a blog posts title or a category title
        $em = $this->getDoctrine()->getManager();
        //first lets see if the last segment is a blog post
        $post = $em->getRepository(Blog::class)->findBy(['systemtitle'=>$segments[$segmentCount-1]]);
        if(is_array($post) && isset($post[0]) && $post[0] instanceof Blog)
        {
            //the last segment is a post!
            //this is where we display the post template
            $blog = $post[0];
            //get the category
            $category = $blog->getCategory();
            //breadcrumbs
            $breadcrumbs->addBreadcrumb('Home', '/');
            $breadcrumbs->addBreadcrumb('Blog', '/blog');
            $category->handleBreadcrumbs($breadcrumbs,'blog', $category);
            $breadcrumbs->setActive($blog->getTitle(), '/blog/'.$path);
            $breadcrumbs->setBreadcrumbs();
            
            $files = $blog->getFiles();
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
                $fotoRama->setId('blogpost');
                $slideshow = $fotoRama->createView();
            }
            $liker->setNode($blog);
            $commentor->setNode($blog);
            $commentor->setUnregisteredUsers(false);
            return $this->render('blog/view_post.html.twig', array(
                'blogPost' => $blog,
                'category' => $category,
                'slideshow' => $slideshow,
                'liker'=>$liker->createView(),
                'commentor'=>$commentor->createView()
            ));
        }
        else
        {
            //check to see if the last segment is a category then.
            $categoryGroup = $em->getRepository(CategoryGroup::class)->findBy(['title'=>'Blog Group']);
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
                    $breadcrumbs->addBreadcrumb('Blog', '/blog');
                    if($category->getParent() != null)
                    {
                        $category->handleBreadcrumbs($breadcrumbs,'blog', $category);
                    }
                    $breadcrumbs->setActive($category->getTitle(), '/blog/'.$path);
                    $breadcrumbs->setBreadcrumbs();

                    $filterizer->setRepo(Blog::class);
                    $filterizer->setListTemplate('blog/blog-post-list-view.html.twig');
                    $filterizer->setGridTemplate('blog/blog-post-grid-view.html.twig');
                    $filterizer->setTableStartTemplate('blog/blog-post-table-start.html.twig');
                    $filterizer->setTableTemplate('blog/blog-post-table-view.html.twig');
                    $filterizer->setTableEndTemplate('blog/blog-post-table-end.html.twig');
                    $filterizer->setPaginationUrl('/blog/'.$category->getGenerateurl() . '?');
                    $filterizer->addSearchField('searchTitle', 'Search By Title', 'title');
                    $filterizer->addWhere('category', $category->getId(), '=');
                    $filterizer->setUseLiker(true);
                    $filterizer->setLiker($liker);
                    $filterizer->handleRequest($request);
                    $filterizer->setContent();
                    $data = $filterizer->getData();
                    $posts = $em->getRepository(Blog::class)->findBy(['category'=>$category->getId()]);
                    $childrenCategories = $category->getChildren();
                    $fotoRama->setId('blogcategory');
                    $fotoRama->setHeight('50%');
                    $fotoRama->initFotorama();
                    return $this->render('blog/view_post.html.twig', array(
                        'childCategories'=>$childrenCategories,
                        'category'=>$category,
                        'filterizer'=>$filterizer->createView(),
                        'posts'=>$posts,
                        'blogPost'=>null
                    ));
                }
            }
            //no category and no posts? This page should not be displayed!!!
            throw new NotFoundHttpException("Page not found");
            
        }
        
    }
    
}