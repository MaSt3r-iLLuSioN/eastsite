<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity\BlockTypes;

use App\Entity\CategoryGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\BlockEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
/**
 * Description of CategoryGroupBlockType
 *
 * @author Trey
 */
/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryGroupBlockTypeEntityRepository")
 */
class CategoryGroupBlockTypeEntity implements BlockTypeEntityInterface {
    //put your code here
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * One Category Group Block has(is) one Block.
     * @ORM\OneToOne(targetEntity="App\Entity\BlockEntity")
     */
     private $block;
     
     /**
     * Many Category Group Blocks can have one Category Group.
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryGroup")
     * @Assert\NotBlank()
     */
     private $categorygroup;
     
     private $em;
     private $container;
     
     public function __construct(EntityManagerInterface $em, ContainerInterface $container)
     {
        $this->em = $em;
        $this->container = $container;
     }
     
     public function getCategorygroup()
     {
         return $this->categorygroup;
     }
     
     public function setCategorygroup(CategoryGroup $group)
     {
         $this->categorygroup = $group;
     }
     
     public function getBlock(): BlockEntity {
        return $this->block;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getParams(): array {
        $params = array();
        $params['categorygroup'] = $this->getCategorygroup()->getId();
        return $params;
    }

    public function getTypeClass(): string {
        return 'CategoryGroup';
    }

    public function saveData(EntityManager $em, Form $form, BlockEntity $block, Request $request, bool $first = false): BlockTypeEntityInterface {
        $this->setBlock($block);
        if($first == true)
        {
            
        }
        else 
        {
            $categoryGroup = $em->getRepository(CategoryGroup::class)->findBy(['id'=>$form->get('categorygroup')->getData()]);
            $this->setCategorygroup($categoryGroup[0]);
        }
        $em->persist($this);
        $em->flush();
        return $this;
    }
    public function removeData(EntityManager $em)
    {
        
    }

    public function setBlock(BlockEntity $block) {
        $this->block = $block;
    }

    public function setForm(FormBuilderInterface $form, ContainerInterface $container): Form  {
        $categoryGroups = $container->get('doctrine.orm.entity_manager')->getRepository(CategoryGroup::class)->findAll();
        $choices = array();
        $val = '';
        if($this->getCategorygroup() != null)
            $val = $this->categorygroup->getId();
        foreach($categoryGroups as $category)
        {
            $choices[$category->getTitle()] = $category->getId();
        }
        return $form->add('categorygroup',ChoiceType::class, array(
            'choices'=> $choices,
            'attr' => array(
                'class' => 'form-control',
                'name' => 'category'
            ),
            'data'=>$val,
        ))->getForm();
    }
    
    public function getCategories()
    {
        return $this->categorygroup->getCategories();
    }

    public function setContainer(ContainerInterface $container) {
        $this->container = $container;
    }

}
