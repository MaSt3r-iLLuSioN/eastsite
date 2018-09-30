<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity\BlockTypes;

use App\Entity\BlockEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of CarouselSlideshow
 *
 * @author Trey
 */
/**
 * @ORM\Entity(repositoryClass="App\Repository\CarouselSlideshowBlockTypeEntityRepository")
 */
class CarouselSlideshowBlockTypeEntity implements BlockTypeEntityInterface{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="integer")
     */
    private $maxphotonumber;
    /**
    * One Category Group Block has(is) one Block.
    * @ORM\OneToOne(targetEntity="App\Entity\BlockEntity")
    */
    private $block;
    private $em;
    private $container;
    public function __construct(EntityManagerInterface $em, ContainerInterface $container) {
        $this->em = $em;
        $this->container = $container;
    }

    public function getBlock(): BlockEntity {
        return $this->block;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getMaxphotonumber()
    {
        if ($this->maxphotonumber != null)
            return $this->maxphotonumber;
        else
            return '1';
    }
    
    public function setMaxphotonumber($maxNum)
    {
        $this->maxphotonumber = $maxNum;
    }
    
    public function getParams(): array {
        $params = array();
        return $params;
    }
    
    public function getFiles()
    {
        
        $files = $this->em->getRepository(\App\Entity\FileEntity::class)->findBy(['slideshowenabled'=>1,'enabled'=>1]);
        shuffle($files);
        $output = array_slice($files, 0, $this->getMaxphotonumber() * 5);
        $newFiles = array();
        foreach($output as $file)
        {
            $tempArray = array();
            $tempArray['description'] = $file->getDescription();
            $tempArray['url'] = $file->getUrl();
            //get the url to the content this file is associated with
            $entityType = $file->getEntity();
            switch($entityType)
            {
                case 'page':
                    $tempArray['content_type'] = 'page';
                    //get all pages
                    $pages = $this->em->getRepository(\App\Entity\PageEntity::class)->findAll();
                    foreach($pages as $page)
                    {
                        if($page->hasFile($file))
                        {
                            $tempArray['content_url'] = $page->getUrl();
                            $tempArray['content_title'] = $page->getTitle();
                            $tempArray['content_description'] = $page->getContentshort();
                        }
                    }
                    break;
                case 'blog':
                    $tempArray['content_type'] = 'blog';
                    //get all posts
                    $posts = $this->em->getRepository(\App\Entity\Blog::class)->findAll();
                    foreach($posts as $post)
                    {
                        if($post->hasFile($file))
                        {
                            $tempArray['content_url'] = $post->getUrl();
                            $tempArray['content_title'] = $post->getTitle();
                            $tempArray['content_description'] = $post->getContentshort();
                        }
                    }
                    break;
                case 'project':
                    $tempArray['content_type'] = 'projects';
                    //get all pages
                    $projects = $this->em->getRepository(\App\Entity\Project::class)->findAll();
                    foreach($projects as $project)
                    {
                        if($project->hasFile($file))
                        {
                            $tempArray['content_url'] = $project->getUrl();
                            $tempArray['content_title'] = $project->getTitle();
                            $tempArray['content_description'] = $project->getContentshort();
                        }
                    }
                    break;
                default:
                    break;
            }
            $newFiles[] = $tempArray;
        }
        return $newFiles;
    }

    public function getTypeClass(): string {
        return 'CarouselSlideshow';
    }

    public function removeData(EntityManager $em) {
        
    }

    public function saveData(EntityManager $em, Form $form, BlockEntity $block, Request $request, bool $first = false): BlockTypeEntityInterface {
        //$this->setBlock($block);
        if ($first == true)
        {
            $maxNumber = $this->getMaxphotonumber();
        }
        else
        {
            $maxNumber = $form->get('maxnumber')->getData();
        }
        $this->setMaxphotonumber($maxNumber);
        $em->persist($this);
        $em->flush();
        return $this;
    }

    public function setBlock(BlockEntity $block) {
        $this->block = $block;
    }

    public function setContainer(ContainerInterface $container) {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
    }

    public function setForm(FormBuilderInterface $form, ContainerInterface $container): Form {
        return $form->add('maxnumber', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                '5'=>'1',
                '10'=>'2',
                '15'=>'3',
                '20'=>'4'
            ),
            'data'=>$this->getMaxphotonumber(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->getForm();
        
    }

}
