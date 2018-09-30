<?php

namespace App\Controller;
use App\Service\Breadcrumbs;
use App\Entity\TabList;
use App\Entity\Tab;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
class TabController extends BaseController
{
    /**
     * @Route("/admin/tabs", name="tabs")
     */
    public function index(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Tab Groups');
        $breadcrumbs->setBreadcrumbs();
        
        $tabGroups = $this->getDoctrine()->getManager()->getRepository(TabList::class)->findAll();
        // replace this line with your own code!
        return $this->render('tabs/index.html.twig',array(
            'tabGroups' => $tabGroups
        ));
    }
    
    /**
     * @Route("/admin/tabs/add-group", name="tabAddGroup")
     */
    public function addGroup(Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Tab Groups', '/admin/tabs');
        $breadcrumbs->setActive('Add Tab Group');
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Tab Group Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $tabGroup = new TabList();
            $tabGroup->setTitle($form->get('title')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($tabGroup);
            $em->flush();
            $this->addFlash('notice', 'Tab Group made successfully, now add some Tabs!');
            return $this->redirectToRoute('tabAddTab', array('id'=>$tabGroup->getId()));
        }
        return $this->render('tabs/add-group.html.twig',array(
            'form' => $form->createView()
        ));
    }
    
    /*
     * @Route("/admin/tabs/{id}/edit}",name="tabEditGroup")
     */
    public function editGroup(TabList $tabGroup, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Tab Groups', '/admin/tabs');
        $breadcrumbs->setActive('Edit Tab Group: '.$tabGroup->getTitle());
        $breadcrumbs->setBreadcrumbs();
    }
    
    /*
     * @Route("/admin/tabs/{id}/delete", name="tabDeleteGroup")
     */
    public function deleteGroup(TabList $tabGroup, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Tab Groups', '/admin/tabs');
        $breadcrumbs->setActive('Delete Tab Group: '.$tabGroup->getTitle());
        $breadcrumbs->setBreadcrumbs();
    }
    
    /**
    * @Route("/admin/tabs/{id}/add", name="tabAddTab")
    */
    public function addTab(TabList $tabGroup, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Tab Groups', '/admin/tabs');
        $breadcrumbs->addBreadcrumb('View Tab Group: '.$tabGroup->getTitle(), '/admin/tabs/'.$tabGroup->getId());
        $breadcrumbs->setActive('Add Tab');
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Tab Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('content', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array(
                    'attr' => array(
                        'placeholder' => 'Tab Content',
                        'class' => 'form-control',
                        'name' => 'content'
                    )
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $tab = new Tab();
            $tab->setTitle($form->get('title')->getData());
            $tab->setContent($form->get('content')->getData());
            $tab->setTab($tabGroup);
            $tab->setMt($this->clean($tab->getTitle()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($tab);
            $em->flush();
            $this->addFlash('notice', 'Tab: '. $tab->getTitle() . ' Added To: ' . $tabGroup->getTitle() .' Successfully!');
            return $this->redirectToRoute('tabViewGroup', array('id'=>$tabGroup->getId()));
        }
        return $this->render('tabs/add-tab.html.twig', array(
            'form'=>$form->createView(),
            'tabGroup' => $tabGroup
        ));
    }
    
    private function clean(string $string,Breadcrumbs $breadcrumbs)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
    
    /**
     * @Route("/admin/tabs/{id}", name="tabViewGroup")
     */
    public function viewTabGroup(TabList $tabGroup,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Tab Groups', '/admin/tabs');
        $breadcrumbs->setActive('View Tab Group: '.$tabGroup->getTitle());
        $breadcrumbs->setBreadcrumbs();
        return $this->render('tabs/view-tab-group.html.twig', array(
            'tabGroup' => $tabGroup
        ));
    }
    /**
     * @Route("/admin/tabs/{tabListId}/delete/{tabId}", name="tabDeleteTab")
     * @ParamConverter("tabGroup", options={"mapping": {"tabListId" : "id"}})
     * @ParamConverter("tab", options={"mapping": {"tabId"   : "id"}})
     */
    public function deleteTab(TabList $tabGroup, Tab $tab, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Tab Groups', '/admin/tabs');
        $breadcrumbs->addBreadcrumb('View Tab Group: '.$tabGroup->getTitle(), '/admin/tabs/'.$tabGroup->getId());
        $breadcrumbs->setActive('Delete Tab: '.$tab->getTitle());
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
            $em->remove($tab);
            $em->flush();
            $this->addFlash('notice', $tab->getTitle() . ' was removed from ' . $tabGroup->getTitle() . ' successfully!');
            return $this->redirectToRoute('tabViewGroup', array('id'=>$tabGroup->getId()));
        }
        
        return $this->render('tabs/delete-tab.html.twig', array(
            'tabGroup' => $tabGroup,
            'tab' => $tab,
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/admin/tabs/{tabListId}/edit/{tabId}", name="tabEditTab")
     * @ParamConverter("tabGroup", options={"mapping": {"tabListId" : "id"}})
     * @ParamConverter("tab", options={"mapping": {"tabId"   : "id"}})
     */
    public function editTab(TabList $tabGroup, Tab $tab, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Tab Groups', '/admin/tabs');
        $breadcrumbs->addBreadcrumb('View Tab Group: '.$tabGroup->getTitle(), '/admin/tabs/'.$tabGroup->getId());
        $breadcrumbs->setActive('Edit Tab: '.$tab->getTitle());
        $breadcrumbs->setBreadcrumbs();
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'data' => $tab->getTitle(),
                    'attr' => array(
                        'placeholder' => 'Tab Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('content', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array(
                    'data' => $tab->getContent(),
                    'attr' => array(
                        'placeholder' => 'Tab Content',
                        'class' => 'form-control',
                        'name' => 'content'
                    )
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $tab->setTitle($form->get('title')->getData());
            $tab->setContent($form->get('content')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($tab);
            $em->flush();
            $this->addFlash('notice', 'Tab edited successfully!');
            return $this->redirectToRoute('tabViewGroup', array('id'=>$tabGroup->getId()));
        }
        
        return $this->render('tabs/edit-tab.html.twig', array(
            'tabGroup' => $tabGroup,
            'tab' => $tab,
            'form' => $form->createView()
        ));
    }
}
