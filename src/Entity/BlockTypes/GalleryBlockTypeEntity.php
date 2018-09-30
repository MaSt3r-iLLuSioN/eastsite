<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Entity\BlockTypes;

use App\Entity\BlockEntity;
use App\Entity\FileEntity;
use App\Form\Type\JQueryFileUploaderType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
/**
 * Description of GalleryBlockTypeEntity
 *
 * @author Trey
 */
/**
 * @ORM\Entity(repositoryClass="App\Repository\GalleryBlockTypeEntityRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class GalleryBlockTypeEntity implements BlockTypeEntityInterface {
    //put your code here
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * One Category Group Block has(is) one Block.
     * @ORM\Cache(usage="READ_ONLY")
     * @ORM\OneToOne(targetEntity="App\Entity\BlockEntity")
     */
     private $block;
     /**
     * Many gallery blocks have Many files.
      * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     * @ORM\ManyToMany(targetEntity="App\Entity\FileEntity")
     * @ORM\JoinTable(name="block_gallery_files",
     *      joinColumns={@ORM\JoinColumn(name="block_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id")}
     *      )
     */
    private $files;
    /**
     * @ORM\Column(type="string")
     */
    private $thumbs;
    /**
     * @ORM\Column(type="string")
     */
    private $stopautoplay;
    /**
     * @ORM\Column(type="string")
     */
    private $captions;
    /**
     * @ORM\Column(type="string")
     */
    private $transition;
    /**
     * @ORM\Column(type="string")
     */
    private $clicktransition;
    /**
     * @ORM\Column(type="string")
     */
    private $click;
    /**
     * @ORM\Column(type="string")
     */
    private $swipe;
    /**
     * @ORM\Column(type="string")
     */
    private $direction;
    /**
     * @ORM\Column(type="string")
     */
    private $fullscreen;
    /**
     * @ORM\Column(type="string")
     */
    private $navposition;
    /**
     * @ORM\Column(type="string")
     */
    private $transitionduration;
    /**
     * @ORM\Column(type="string")
     */
    private $controls;
    /**
     * @ORM\Column(type="string")
     */
    private $keyboard;
    /**
     * @ORM\Column(type="string")
     */
    private $autoplay;
    
    
    private $em;
    private $container;
    private $fotorama;
    
    public function __construct(EntityManagerInterface $em, ContainerInterface $container) {
        $this->em = $em;
        $this->files = new ArrayCollection();
        $this->container = $container;
        $this->fotorama = $this->container->get('app.fotorama');
    }
    public function getKeyboard(): string
    {
        if($this->keyboard != null)
            return $this->keyboard;
        else
            return 'false';
    }
    public function setKeyboard(string $keyboard)
    {
        $this->keyboard = $keyboard;
    }
    public function getStopautoplay(): string
    {
        if($this->stopautoplay != null)
            return $this->stopautoplay;
        else
            return 'true';
    }
    public function setStopautoplay(string $stopautoplay)
    {
        $this->stopautoplay = $stopautoplay;
    }
    public function getClick():string
    {
        if($this->click != null)
            return $this->click;
        else
            return 'true';
    }
    public function setClick(string $click)
    {
        $this->click = $click;
    }
    public function getSwipe(): string
    {
        if($this->swipe != null)
            return $this->swipe;
        else
            return 'true';
    }
    public function setSwipe(string $swipe)
    {
        $this->swipe = $swipe;
    }
    public function getClicktransition(): string
    {
        if($this->clicktransition != null)
            return $this->clicktransition;
        else
            return 'slide';
    }
    public function setClicktransition(string $clicktransition)
    {
        $this->clicktransition = $clicktransition;
    }
    public function getNavposition(): string
    {
        if($this->navposition != null)
            return $this->navposition;
        else
            return 'Bottom';
    }
    public function setNavposition(string $navposition)
    {
        $this->navposition = $navposition;
    }
    public function getDirection(): string
    {
        if($this->direction != null)
            return $this->direction;
        else
            return 'ltr';
    }
    public function setDirection(string $direction)
    {
        $this->direction = $direction;
    }
    public function getTransition(): string
    {
        if($this->transition != null)
            return $this->transition;
        else
            return 'slide';
    }
    public function setTransition(string $transition)
    {
        $this->transition = $transition;
    }
    public function getCaptions(): string
    {
        if($this->captions != null)
            return $this->captions;
        else
            return 'true';
    }
    public function setCaptions(string $captions)
    {
        $this->captions = $captions;
    }
    public function getTransitionduration():string
    {
        if($this->transitionduration != null)
            return $this->transitionduration;
        else
            return '1000';
    }
    public function setTransitionduration(string $transitionDuration)
    {
        $this->transitionduration = $transitionDuration;
    }
    public function getAutoplay(): string
    {
        if($this->autoplay != null)
            return $this->autoplay;
        else
            return 'true';
    }
    public function setAutoplay(string $autoplay)
    {
        $this->autoplay = $autoplay;
    }
    public function getThumbs() : string
    {
        if($this->thumbs != null)
            return $this->thumbs;
        else
            return 'false';
    }
    public function setThumbs(string $thumbs)
    {
        $this->thumbs = $thumbs;
    }
    public function getFullscreen() : string
    {
        if($this->fullscreen != null)
            return $this->fullscreen;
        else
            return 'true';
    }
    public function setFullscreen(string $fullscreen)
    {
        $this->fullscreen = $fullscreen;
    }
    public function getControls(): string
    {
        if($this->controls != null)
            return $this->controls;
        else
            return 'true';
    }
    public function setControls(string $controls)
    {
        $this->controls = $controls;
    }
    public function getFiles()
    {
        $this->fotorama = $this->container->get('app.fotorama');
        if($this->fotorama instanceof \App\Service\Fotorama)
        {
            //configure fotorama service
            $this->fotorama->setId($this->getId());
            $this->fotorama->nav = $this->getThumbs();
            $this->fotorama->navPosition = $this->getNavposition();
            $this->fotorama->allowFullScreen = $this->getFullscreen();
            $this->fotorama->transition = $this->getTransition();
            $this->fotorama->clickTransition = $this->getClicktransition();
            $this->fotorama->transitionDuration = $this->getTransitionduration();
            $this->fotorama->captions = $this->getCaptions();
            $this->fotorama->autoPlay = $this->getAutoplay();
            $this->fotorama->stopAutoplayOnTouch = $this->getStopautoplay();
            $this->fotorama->keyboard = $this->getKeyboard();
            $this->fotorama->arrows = $this->getControls();
            $this->fotorama->click = $this->getClick();
            $this->fotorama->swipe = $this->getSwipe();
            $this->fotorama->direction = $this->getDirection();
            $this->fotorama->initFotorama();
        }
        return $this->files;
    }
    
    public function getAllFiles()
    {
        return $this->files;
    }
    
    public function addFile(FileEntity $file)
    {
        if(!$this->files->contains($file))
        {
            $this->files->add($file);
        }
    }
    
    public function removeFile(FileEntity $file): bool
    {
        if($this->files->contains($file))
        {
            $this->files->removeElement($file);
            return true;
        }
        return false;
    }

    public function getBlock(): BlockEntity {
        return $this->block;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getParams(): array {
        $params = array();
        $params['files'] = $this->getFiles();
        return $params;
    }

    public function getTypeClass(): string {
        return 'Gallery';
    }

    public function saveData(EntityManager $em, Form $form, BlockEntity $block, Request $request, bool $first = false): BlockTypeEntityInterface 
    {    
        //we need to first remove all the files associated with this block
        $blockFiles = $this->getAllFiles();
        if($blockFiles instanceof \Doctrine\ORM\PersistentCollection)
        {
            foreach($blockFiles as $file)
            {
                if($file instanceof FileEntity)
                {
                    $this->removeFile($file);
                }
            }
        }
        
        $postFiles = $request->get('postfiles');
        if(is_array($postFiles))
        {
            foreach($postFiles as $fileId)
            {
                $fileEntity = $em->getRepository(FileEntity::class)->find($fileId);
                if($fileEntity instanceof FileEntity)
                {
                    $this->addFile($fileEntity);
                }
            }
        }
        if($first == true)
        {
            $navType = $this->getThumbs();
            $navPosition = $this->getNavposition();
            $fullscreen = $this->getFullscreen();
            $transition = $this->getTransition();
            $clickTransition = $this->getClicktransition();
            $transitionDuration = $this->getTransitionduration();
            $captions = $this->getCaptions();
            $autoplay = $this->getAutoplay();
            $stopAutoplay = $this->getStopautoplay();
            $keyboard = $this->getKeyboard();
            $arrows = $this->getControls();
            $click = $this->getClick();
            $swipe = $this->getSwipe();
            $direction = $this->getDirection();
        }
        else
        {
            $navType = $form->get('navType')->getData();
            $navPosition = $form->get('navPosition')->getData();
            $fullscreen = $form->get('fullscreen')->getData();
            $transition = $form->get('transition')->getData();
            $clickTransition = $form->get('clicktransition')->getData();
            $transitionDuration = $form->get('transitionDuration')->getData();
            $captions = $form->get('captions')->getData();
            $autoplay = $form->get('autoplay')->getData();
            $stopAutoplay = $form->get('stopautoplay')->getData();
            $keyboard = $form->get('keyboard')->getData();
            $arrows = $form->get('arrows')->getData();
            $click = $form->get('click')->getData();
            $swipe = $form->get('swipe')->getData();
            $direction = $form->get('direction')->getData();
        }
        //var_dump($navType . $navPosition . $fullscreen . $transition . $clickTransition . $transitionDuration . $captions . $autoplay . $stopAutoplay . $keyboard . $arrows . $click . $swipe . $direction);
        $this->setThumbs($navType);
        $this->setNavposition($navPosition);
        $this->setFullscreen($fullscreen);
        $this->setTransition($transition);
        $this->setClicktransition($clickTransition);
        $this->setTransitionduration($transitionDuration);
        $this->setCaptions($captions);
        $this->setAutoplay($autoplay);
        $this->setStopautoplay($stopAutoplay);
        $this->setKeyboard($keyboard);
        $this->setControls($arrows);
        $this->setClick($click);
        $this->setSwipe($swipe);
        $this->setDirection($direction);
        
        $em->persist($this);
        $em->flush();
        return $this;
    }
    
    public function removeData(EntityManager $em)
    {
        if($this->files instanceof \Doctrine\ORM\PersistentCollection || is_array($this->files))
        {
            foreach($this->files as $file)
            {
                if($file instanceof FileEntity)
                {
                    $this->removeFile($file);
                    $em->persist($this);
                    $em->flush();
                    $file->removeFile($this->container->getParameter('upload_directory'));
                    $em->remove($file);
                    $em->flush();
                }
            }
        }
        $fs = new Filesystem();
        $fs->remove($this->container->getParameter('upload_directory') . '/'.'galleryblock/'.$this->getId());
    }

    public function setBlock(BlockEntity $block) {
        $this->block = $block;
    }

    public function setForm(FormBuilderInterface $form, ContainerInterface $container): Form {
        $fileUtility = $container->get('app.fileUtility');
        $existingFiles = $fileUtility->makeExistingFilesArray($this->files, $container->getParameter('upload_directory'));
        return $form->add('navType', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Dots'=>'dots',
                'Thumbs'=>'thumbs',
                'None'=>'false'
            ),
            'data'=>$this->getThumbs(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))
        ->add('navPosition', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Top'=>'top',
                'Bottom'=>'bottom'
            ),
            'data'=>$this->getNavposition(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('fullscreen', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Yes'=>'true',
                'No'=>'false'
            ),
            'data'=>$this->getFullscreen(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('transition', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Slide'=>'slide',
                'Crossfade'=>'crossfade',
                'Dissolve'=>'dissolve'
            ),
            'data'=>$this->getTransition(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('clicktransition', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Slide'=>'slide',
                'Crossfade'=>'crossfade',
                'Dissolve'=>'dissolve'
            ),
            'data'=>$this->getClicktransition(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('transitionDuration', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                '1 sec'=>'1000',
                '2 sec'=>'2000',
                '3 sec'=>'3000'
            ),
            'data'=>$this->getTransitionduration(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('captions', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Yes'=>'true',
                'No'=>'false'
            ),
            'data'=>$this->getCaptions(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('autoplay', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Yes'=>'true',
                'No'=>'false'
            ),
            'data'=>$this->getAutoplay(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('stopautoplay', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Yes'=>'true',
                'No'=>'false'
            ),
            'data'=>$this->getStopautoplay(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('keyboard', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Yes'=>'true',
                'No'=>'false'
            ),
            'data'=>$this->getKeyboard(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('arrows', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Yes'=>'true',
                'No'=>'false'
            ),
            'data'=>$this->getControls(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('click', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Yes'=>'true',
                'No'=>'false'
            ),
            'data'=>$this->getClick(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('swipe', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Yes'=>'true',
                'No'=>'false'
            ),
            'data'=>$this->getSwipe(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('direction', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array(
            'choices'=>array(
                'Left To Right'=>'ltr',
                'Right To Left'=>'rtl'
            ),
            'data'=>$this->getDirection(),
            'attr'=>array(
                'class' => 'form-control',
            ),
        ))->add('postfile', JQueryFileUploaderType::class, array(
            'twig'=>$container->get('twig'),
            'dispatcher'=>$container->get('event_dispatcher'),
            'PrettyPhoto'=>$container->get('app.prettyPhoto'),
            'uploadUrl'=>'/file/upload/gallery-block/galleryblock-'.$this->block->getId().'/postfile',
            'hiddenFieldName' => 'postfiles',
            'existingFiles'=>$existingFiles,
            'id'=>'form_postfile',
            'label'=>'Gallery Images',
            'required'=>false,
            'fileTypes'=>array(
                'png','jpg','jpeg','gif'
            ),
            'attr'=>array(
                'class' => 'form-control',
                'name' => 'postfile',
            )
        ))->getForm();
    }

    public function setContainer(ContainerInterface $container) {
        $this->container = $container;
    }

}
