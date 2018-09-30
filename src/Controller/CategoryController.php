<?php

namespace App\Controller;
use App\Service\Breadcrumbs;
use App\Entity\CategoryGroup;
use App\Entity\CategoryEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CategoryController extends BaseController
{
    /**
     * @Route("/admin/category/add/group", name="addCategoryGroup")
     */
    public function addCategoryGroup(Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('add_category_group', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Category Groups', '/admin/category');
        $breadcrumbs->setActive('Add Category Group');
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Category Group Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $categoryGroup = new CategoryGroup();
            $categoryGroup->setTitle($form->get('title')->getData());
            $categoryGroup->setSystem(false);
            $em = $this->getDoctrine()->getManager();
            $em->persist($categoryGroup);
            $em->flush();
            $this->addFlash('notice', 'Category Group: '.$categoryGroup->getTitle().' was made successfully!');
            return $this->redirectToRoute('viewCategoryGroup', array('group'=>$categoryGroup->getId()));
        }
        return $this->render('categories/add-category-group.html.twig',array(
            'form'=>$form->createView()
        ));
    }
    /**
     * @Route("/admin/category/{group}/edit", name="editCategoryGroup")
     */
    public function editCategoryGroup(CategoryGroup $group, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('edit_category_group', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Category Groups', '/admin/category');
        $breadcrumbs->addBreadcrumb('View Category Group: '.$group->getTitle(), '/admin/category/'.$group->getId());
        $breadcrumbs->setActive('Edit Category Group');
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Category Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    ),
                    'data'=>$group->getTitle()
                ))
                
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $group->setTitle($form->get('title')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();
            $this->addFlash('notice', 'Category Group: '.$group->getTitle().' was edited successfully!');
            return $this->redirectToRoute('viewCategoryGroup', array('group'=>$group->getId()));
        }
        return $this->render('categories/edit-category-group.html.twig',array(
            'form'=>$form->createView(),
            'group'=>$group
        ));
    }
    /**
     * @Route("/admin/category/{group}/delete", name="deleteCategoryGroup")
     */
    public function deleteCategoryGroup(CategoryGroup $group, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('delete_category_group', null);
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Category Groups', '/admin/category');
        $breadcrumbs->addBreadcrumb('View Category Group: '.$group->getTitle(), '/admin/category/'.$group->getId());
        $breadcrumbs->setActive('Delete Category Group');
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
            foreach($group->getCategories() as $category)
            {
                $em->remove($category);
                $em->flush();
            }
            $em->remove($group);
            $em->flush();
            
            $this->addFlash('notice', 'Category Group: '.$group->getTitle() . ' was removed successfully!');
            return $this->redirectToRoute('viewCategoryGroups');
        }
        return $this->render('categories/delete-category-group.html.twig', array(
            'group' => $group,
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/admin/category/{group}", name="viewCategoryGroup")
     */
    public function viewCategoryGroup(CategoryGroup $group,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('view_admin_category_group_area', null);
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Category Groups', '/admin/category');
        $breadcrumbs->setActive('View Category Group: '.$group->getTitle());
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $parentCategories = $em->getRepository(CategoryEntity::class)->findBy
            ([
                'categorygroup'=>$group->getId(),
                'parent'=>null
            ]);
        return $this->render('categories/view_category_group.html.twig',array(
            'group'=>$group,
            'categories'=>$parentCategories
        ));
    }
    /**
     * @Route("/admin/category/{group}/add", name="addCategoryToGroup")
     */
    public function addCategoryToGroup(CategoryGroup $group, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('add_category', null);
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Category Groups', '/admin/category');
        $breadcrumbs->addBreadcrumb('View Category Group: '.$group->getTitle(), '/admin/category/'.$group->getId());
        $breadcrumbs->setActive('Add Category');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $parentCategories = $em->getRepository(CategoryEntity::class)->findBy
            ([
                'categorygroup'=>$group->getId(),
                'parent'=>null
            ]);
        $catChoices = array();
        $catChoices = $this->buildRecursiveList($parentCategories, true);
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        //get all categories in this category group
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Category Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('parentcategory', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                    'choices'=> $catChoices,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'category'
                    )
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $category = new CategoryEntity();
            $category->setTitle($form->get('title')->getData());
            $title = $category->getTitle();
            $title = str_replace(' ', '-', strtolower($title));
            $category->setSystemtitle($title);
            $category->setCategorygroup($group);
            $parentId = $form->get('parentcategory')->getData();
            if($parentId != 0)
            {
                $parent = $em->getRepository(CategoryEntity::class)->findBy(['id'=>$parentId]);
                $category->addParent($parent[0]);
                $category->setUrl($parent->getUrl . '/' . $category->getSystemtitle());
            }
            else
            {
                $category->setUrl('/'. $category->getSystemtitle());
            }
            $category->setSystem(false);
            $em->persist($category);
            $em->flush();
            
            if($parentId != 0)
            {
                $parent[0]->addChild($category);
                $em->persist($parent[0]);
                $em->flush();
            }
            $this->addFlash('notice', 'Category: '.$category->getTitle().' was add to Category Group: '.$group->getTitle().' successfully!');
            return $this->redirectToRoute('viewCategoryGroup', array('group'=>$group->getId()));
        }
        return $this->render('categories/add-category-group-category.html.twig',array(
            'form'=>$form->createView(),
            'group'=>$group
        ));
    }
    
    private function buildRecursiveList($categories, bool $firstRun = false, CategoryEntity $defaultCategory = null, bool $parent = true, $var = null, int $count = 0)
    {
        if($var == null)
            $var = array();
        if($firstRun == true)
            $var['NONE'] = 0;
        foreach($categories as $category)
        {
            if($defaultCategory != null && $defaultCategory->getTitle() == $category->getTitle())
                continue;
            $beginingString = '';
            for($i=0;$i<$count;$i++)
            {
                $beginingString .= '-';
            }
            $var[$beginingString . $category->getTitle()] = $category->getId();
            $children = $category->getChildren();
            
            if($children instanceof \Doctrine\ORM\PersistentCollection && $children->count() > 0)
            {
                $newCount = $count + 1;
                $var = $this->buildRecursiveList($children, false, $defaultCategory, false, $var, $newCount);
            }
        }
        return $var;
    }
    /**
     * @Route("/admin/category/{group}/edit/{category}", name="editGroupCategory")
     */
    public function editGroupCategory(CategoryGroup $group, CategoryEntity $category, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('edit_category', null);
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Category Groups', '/admin/category');
        $breadcrumbs->addBreadcrumb('View Category Group: '.$group->getTitle(), '/admin/category/'.$group->getId());
        $breadcrumbs->setActive('Edit Category: '.$category->getTitle());
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $parentCategories = $em->getRepository(CategoryEntity::class)->findBy
            ([
                'categorygroup'=>$group->getId(),
                'parent'=>null
            ]);
        $val = '';
        if($category->getParent() == null)
        {
            $val .= '0';
        }
        else
        {
            $val .= $category->getParent()->getId();
        }
        $catChoices = array();
        $catChoices = $this->buildRecursiveList($parentCategories, true,$category);
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Category Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    ),
                    'data'=>$category->getTitle()
                ))
                ->add('parentcategory', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
                    'choices'=> $catChoices,
                    'data'=>$val,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'category'
                    )
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $category->setTitle($form->get('title')->getData());
            $title = $category->getTitle();
            $title = str_replace(' ', '-', strtolower($title));
            $category->setSystemtitle($title);
            $parentId = $form->get('parentcategory')->getData();
            if($parentId != 0)
            {
                $parent = $category->getParent();
                if($parent != null && $parent instanceof CategoryEntity)
                {
                    //remove the category from parents children
                    $parent->removeChild($category);
                    $em->persist($parent);
                    $em->flush();
                    $category->removeParent();
                }
                $parent = $em->getRepository(CategoryEntity::class)->findBy(['id'=>$parentId]);
                $category->addParent($parent[0]);
                $category->setUrl($parent[0]->getUrl() . '/' . $category->getSystemtitle());
            }
            else
            {
                $parent = $category->getParent();
                if($parent != null && $parent instanceof CategoryEntity)
                {
                    //remove the category from parents children
                    $parent->removeChild($category);
                    $em->persist($parent);
                    $em->flush();
                    $category->removeParent();
                }
                $category->setUrl('/'. $category->getSystemtitle());
            }
            $children = $category->getChildren();
            foreach($children as $child)
            {
                $child->setUrl($category->getUrl() . '/' . $child->getSystemtitle());
                $em->persist($child);
                $em->flush();
                $childsChildren = $child->getChildren();
                if($childsChildren != null)
                    $this->updateChildrenUrlRecursive ($childsChildren, $em);
            }
            $em->persist($category);
            $em->flush();
            $this->addFlash('notice', 'Category: '.$category->getTitle().' was edited successfully!');
            return $this->redirectToRoute('viewCategoryGroup', array('group'=>$group->getId()));
        }
        return $this->render('categories/edit-category-group-category.html.twig',array(
            'form'=>$form->createView(),
            'group'=>$group,
            'category'=>$category
        ));
    }
    
    private function updateChildrenUrlRecursive(\Doctrine\ORM\PersistentCollection $children, \Doctrine\ORM\EntityManagerInterface $em)
    {
        foreach($children as $child)
        {
            if($child instanceof CategoryEntity)
            {
                $child->setUrl($child->getParent()->getUrl() . '/' . $child->getSystemtitle());
                $em->persist($child);
                $em->flush();
                $childsChildren = $child->getChildren();
                if($childsChildren != null)
                    $this->updateChildrenUrlRecursive ($childsChildren, $em);
            }
        }
    }
    /**
     * @Route("/admin/category/{group}/delete/{category}", name="deleteGroupCategory")
     */
    public function deleteGroupCategory(CategoryGroup $group, CategoryEntity $category, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('delete_category', null);
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Category Groups', '/admin/category');
        $breadcrumbs->addBreadcrumb('View Category Group: '.$group->getTitle(), '/admin/category/'.$group->getId());
        $breadcrumbs->setActive('Delete Category: '.$category->getTitle());
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
            $children = $category->getChildren();
            if($children instanceof \Doctrine\ORM\PersistentCollection && $children->count() > 0)
            {
                foreach($children as $child)
                {
                    $child->removeParent();
                    $em->persist($child);
                    $em->flush();
                }
            }
            $em->remove($category);
            $em->flush();
            
            $this->addFlash('notice', 'Category: '.$category->getTitle() . ' was deleted successfully!');
            return $this->redirectToRoute('viewCategoryGroup', array('group'=>$group->getId()));
        }
        return $this->render('categories/delete-category-group-category.html.twig', array(
            'group' => $group,
            'category'=>$category,
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Route("/admin/category/", name="viewCategoryGroups")
     */
    public function viewCategoryGroups(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('view_admin_category_group_area', null);
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Category Groups');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $groups = $em->getRepository(CategoryGroup::class)->findAll();
        return $this->render('categories/view-category-groups.html.twig', array(
            'groups' => $groups
        ));
    }
}
