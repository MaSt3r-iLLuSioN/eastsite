<?php

namespace App\Controller;
use App\Service\Breadcrumbs;
use App\Entity\BlockTypeEntity;
use App\Entity\BlockEntity;
use App\Entity\RegionEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BlockController extends BaseController
{
    /**
     * @Route("/admin/blocks", name="viewBlocks")
     */
    public function viewBlocks(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('admin_view_blocks', null);
        //breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Blocks');
        $breadcrumbs->setBreadcrumbs();
        $em = $this->getDoctrine()->getManager();
        //get all regions
        $regions = $em->getRepository(RegionEntity::class)->findAll();
        // replace this line with your own code!
        $disabledBlocks = $em->getRepository(BlockEntity::class)->findBy(['enabled'=>false]);
        return $this->render('blocks/view-blocks.html.twig', array(
            'regions'=>$regions,
            'disabledBlocks' => $disabledBlocks,
        ));
    }
    
    /**
     * @Route("/admin/blocks/add", name="addBlock")
     */
    public function addBlock(Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        //deny user access
        $this->denyAccessUnlessGranted('create_blocks', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Blocks','/admin/blocks');
        $breadcrumbs->setActive('Add Block');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        //get available block types
        $types = $em->getRepository(BlockTypeEntity::class)->findAll();
        $typeChoices = array();
        foreach($types as $type)
        {
            $typeChoices[$type->getTitle()] = $type->getId();
        }
        //get regions
        $regions = $em->getRepository(RegionEntity::class)->findAll();
        $regionChoices = array();
       
        foreach($regions as $region)
        {
            $regionChoices[$region->getTitle()] = $region->getId();
        }
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
                        'placeholder' => 'Block Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('type', ChoiceType::class, array(
                    'choices'=> $typeChoices,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'type'
                    )
                ))
                ->add('region', ChoiceType::class, array(
                    'choices'=> $regionChoices,
                    'multiple' => true,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'region'
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
                ->add('onpage', ChoiceType::class,array(
                    'choices'=>array(
                        ' Show On Listed Pages'=>true,
                        'Show On All Pages Except Listed Pages'=>false
                    ),
                    'label'=>'Show On Listed Pages?',
                    'attr'=>array(
                        'class' => 'form-control',
                        'name' =>'onpage'
                    ),
                    'expanded' => true,
                    'multiple' => false
                ))
                ->add('showtitle', ChoiceType::class,array(
                    'choices'=>array(
                        'Yes'=>true,
                        'No'=>false,
                    ),
                    'label'=>'Show Title?',
                    'attr'=>array(
                        'class'=>'form-control',
                        'name'=>'showtitle'
                    ),
                ))
                ->add('listedpages', TextareaType::class, array(
                    'attr'=>array(
                        'placeholder'=> 'List of page urls seperated by comma',
                        'class'=>'form-control',
                        'name'=>'listedpages'
                    )
                ))
                ->add('cssclass', TextType::class, array(
                    'attr'=>array(
                        'placeholder'=> 'Css Class',
                        'class'=>'form-control',
                        'name'=>'cssclass'
                    )
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $block = new BlockEntity();
            $block->setTitle($form->get('title')->getData());
            $block->setShowtitle($form->get('showtitle')->getData());
            $block->setCssclass($form->get('cssclass')->getData());
            $block->setListedPages($form->get('listedpages')->getData());
            $block->setOnpage($form->get('onpage')->getData());
            //get the block type
            $type = $em->getRepository(BlockTypeEntity::class)->find($form->get('type')->getData());
            $block->setType($type);
            //get the block region
            $block->setPriority($form->get('priority')->getData());
            $block->setTypeset(0);
            $block->setEnabled(false);
            $em->persist($block);
            $em->flush();
            foreach($form->get('region')->getData() as $regionId)
            {
                $region = $em->getRepository(RegionEntity::class)->find($regionId);
                $region->addBlock($block);
                $em->persist($region);
                $em->flush();
            }
            $em->persist($block);
            $em->flush();
            $this->addFlash('notice', 'Block ' . $block->getTitle() . ' was made successfully.');
            return $this->redirectToRoute('editBlock', array('block'=>$block->getId()));
        }
        return $this->render('blocks/add-block.html.twig',array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/admin/blocks/{block}/edit", name="editBlock")
     */
    public function editBlock(BlockEntity $block, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('edit_blocks', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Blocks','/admin/blocks');
        $breadcrumbs->setActive('Edit Block: '.$block->getTitle());
        $breadcrumbs->setBreadcrumbs();
        //this is were we will get a custom form for the block type (makes it easier to add different types)
        $em = $this->getDoctrine()->getManager();
        
        //get regions
        $regions = $em->getRepository(RegionEntity::class)->findAll();
        $regionChoices = array();
        $blockRegions = $block->getRegions();
        $regionValues = array();
        foreach($blockRegions as $region)
        {
            $regionValues[] = $region->getId();
        }
        foreach($regions as $region)
        {
            $regionChoices[$region->getTitle()] = $region->getId();
        }
        //priority choices
        $priorityChoices = array();
        for($i = 0; $i <= 10; $i++)
        {
            $priorityChoices[$i] = $i;
        }
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder();
        $form->add('title', TextType::class, array(
            'data'=>$block->getTitle(),
            'attr' => array(
                'placeholder' => 'Block Title',
                'class' => 'form-control',
                'name' => 'title'
            )
        ))
        ->add('region', ChoiceType::class, array(
            'choices'=> $regionChoices,
            'multiple' => true,
            'attr' => array(
                'class' => 'form-control',
                'name' => 'region'
            ),
            'data'=>$regionValues
        ))
        ->add('priority', ChoiceType::class, array(
            'choices'=> $priorityChoices,
            'multiple' => false,
            'attr' => array(
                'class' => 'form-control',
                'name' => 'priority'
            ),
            'data'=>$block->getPriority()
        ))
        ->add('onpage', ChoiceType::class,array(
            'choices'=>array(
                ' Show On Listed Pages '=>true,
                ' Show On All Pages Except Listed Pages '=>false
            ),
            'data'=>$block->getOnpage(),
            'label'=>'Show On Listed Pages?',
            'attr'=>array(
                'class' => 'form-control',
                'name' =>'onpage'
            ),
            'expanded' => true,
            'multiple' => false
        ))
        ->add('showtitle', ChoiceType::class,array(
            'choices'=>array(
                'Yes'=>true,
                'No'=>false,
            ),
            'label'=>'Show Title?',
            'attr'=>array(
                'class'=>'form-control',
                'name'=>'showtitle'
            ),
            'data'=>$block->getShowtitle(),
        ))
        ->add('listedpages', TextareaType::class, array(
            'data'=>$block->getListedpages(),
            'attr'=>array(
                'placeholder'=> 'Listed Pages block should or should not display on. (seperated by comma)',
                'class'=>'form-control',
                'name'=>'listedpages'
            )
        ))
        ->add('cssclass', TextType::class, array(
            'data'=>$block->getCssclass(),
            'attr'=>array(
                'placeholder'=> 'Css Class',
                'class'=>'form-control',
                'name'=>'cssclass'
            )
        ))
        ->add('enabled', ChoiceType::class,array(
            'data'=>$block->getEnabled(),
            'choices'=>array(
                ' True '=>true,
                ' False '=>false
            ),
            'label'=>'Block Enabled',
            'attr'=>array(
                'class' => 'form-control',
                'name' =>'enabled'
            ),
            'expanded' => true,
            'multiple' => false
        ));
        
        
        //the notorious block bug fix!!!!
        $blockTypeClass = '\\App\\Entity\\BlockTypes\\' . $block->getType()->getClass() . 'BlockTypeEntity';
        if($block->getTypeset() == 0)
        {
            $blockClass = new $blockTypeClass($this->getDoctrine()->getManager(), $this->container);
        }
        else
        {
            //not a error (netbeans ide doesnt know lol!)
            $blockClassName = $block->getType()->getClass() . 'BlockTypeEntity';
            $blockClassTmp = $em->getRepository('\\App\\Entity\\BlockTypes\\' . $blockClassName)->findBy(array('block'=> $block->getId()));
            $blockClass = $blockClassTmp[0];
        }
        $blockClass->setBlock($block);
        if($block->getTypeset() != 1)
        {
            $block->setTypeset(1);
            $blockClass = $blockClass->saveData($em, $form->getForm(), $block, $request,true);
            //get the BlockTypeEntity for this block type
            $type = $em->getRepository(BlockTypeEntity::class)->findBy(array('class'=>$blockClass->getTypeClass()));
            $block->setType($type[0]);
            $em->persist($block);
            $em->flush();
            //endfix
        }
        
        $form = $blockClass->setForm($form,$this->container);
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {  //we need to save the block and the new blocktype
            $block->setTitle($form->get('title')->getData());
            $block->setShowtitle($form->get('showtitle')->getData());
            $block->setCssclass($form->get('cssclass')->getData());
            $block->setListedPages($form->get('listedpages')->getData());
            $block->setOnpage($form->get('onpage')->getData());
            $block->setEnabled($form->get('enabled')->getData());
            $block->setPriority($form->get('priority')->getData());
            $block->setTypeset(1);
            $em->persist($block);
            $em->flush();
            $blockClass = $blockClass->saveData($em, $form, $block, $request);
            //get the BlockTypeEntity for this block type
            $type = $em->getRepository(BlockTypeEntity::class)->findBy(array('class'=>$blockClass->getTypeClass()));
            $block->setType($type[0]);
            $allRegions = $em->getRepository(RegionEntity::class)->findAll();
            foreach($allRegions as $allRegion)
            {
                //remove this block from all regions
                $allRegion->removeBlock($block);
                $em->persist($allRegion);
                $em->flush();
            }
            $blockRegions = $form->get('region')->getData();
            foreach($blockRegions as $regionId)
            {
                $region = $em->getRepository(RegionEntity::class)->find($regionId);
                $region->addBlock($block);
                $em->persist($region);
                $em->flush();
            }
            $em->persist($block);
            $em->flush();
            $this->addFlash('notice', 'Block '.$block->getTitle() . ' edited successfully!');
            return $this->redirectToRoute('viewBlock', array('block'=>$block->getId()));
            
        }
        return $this->render('blocks/edit-block.html.twig',array(
            'form' => $form->createView(),
            'block'=>$block,
            'formtemplate'=>$block->getType()->getFormtemplate()
        ));
    }
    
    /**
     * @Route("/admin/blocks/{block}/delete", name="deleteBlock")
     */
    public function deleteBlock(BlockEntity $block, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('delete_blocks', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Blocks','/admin/blocks');
        $breadcrumbs->setActive('Delete Block: '.$block->getTitle());
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
            $regions = $em->getRepository(RegionEntity::class)->findAll();
            foreach($regions as $region)
            {
                $region->removeBlock($block);
                $em->persist($region);
                $em->flush();
            }
            $blockClass = $block->getBlockclass($em, $this->container);
            $blockClass->removeData($em);
            $em->remove($blockClass);
            $em->flush();
            $em->remove($block);
            $em->flush();
            $this->addFlash('notice', 'Block: '.$block->getTitle() . ' was removed successfully!');
            return $this->redirectToRoute('viewBlocks');
        }
        
        return $this->render('blocks/delete-block.html.twig', array(
            'block' => $block,
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Route("/admin/blocks/{block}/view", name="viewBlock")
     */
    public function viewBlock(BlockEntity $block,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $this->denyAccessUnlessGranted('admin_view_block', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Blocks','/admin/blocks');
        $breadcrumbs->setActive('View Block: '.$block->getTitle());
        $breadcrumbs->setBreadcrumbs();
        $em = $this->getDoctrine()->getManager();
        $block->setBlockClass($em, $this->container);
        return $this->render('blocks/view-block.html.twig',array(
            'block'=>$block
        ));
    }
}
