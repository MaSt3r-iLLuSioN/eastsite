<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\BlockEntityRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class BlockEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Cache(usage="READ_ONLY")
    * @ORM\ManyToMany(targetEntity="App\Entity\RegionEntity", mappedBy="blocks")
    */
    private $regions;

    /**
    * Many Blocks can have one BlockType.
     * @ORM\Cache(usage="READ_ONLY")
    * @ORM\ManyToOne(targetEntity="App\Entity\BlockTypeEntity")
    */
    private $type;

    /**
    * @ORM\Column(type="string")
    * @Assert\NotBlank()
    */
    private $title;

    /**
    * @ORM\Column(type="string")
    */
    private $cssclass;

    /**
    * @ORM\Column(type="text")
    * @Assert\NotBlank()
    */
    private $listedpages;

    /**
    * @ORM\Column(type="boolean", unique=false)
    * @Assert\NotBlank()
    */
    private $onpage;

    /**
    * @ORM\Column(type="boolean", unique=false)
    * @Assert\NotBlank()
    */
    private $showtitle;

    /**
    * @ORM\Column(type="boolean", unique=false)
    * @Assert\NotBlank()
    */
    private $enabled;

    /**
    * @ORM\Column(type="integer")
    * @Assert\NotBlank()
    */
    private $priority;

    /**
    * @ORM\Column(type="integer")
    * @Assert\NotBlank()
    */
    private $typeset;

    public $blockclass;
    public function __construct()
    {
        $this->regions = new ArrayCollection();
    }

    public function getId() : int
    {
        return $this->id;
    }
    public function getShowtitle(): bool
    {
        return $this->showtitle;
    }
    public function setShowtitle(bool $showtitle)
    {
        $this->showtitle = $showtitle;
    }
    public function getEnabled() : bool
    {
        return $this->enabled;
    }
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }
    public function getTypeset() : int
    {
        return $this->typeset;
    }

    public function setTypeset(int $typeset)
    {
        $this->typeset = $typeset;
    }

    public function getCssclass() : string
    {
        return $this->cssclass;
    }

    public function setCssclass(string $cssclass)
    {
        $this->cssclass = $cssclass;
    }

    public function getListedpages() : string
    {
        return $this->listedpages;
    }

    public function setListedPages(string $listedpages)
    {
        $this->listedpages = $listedpages;
    }
    public function getOnpage() : bool
    {
        return $this->onpage;
    }
    public function setOnpage(bool $onpage)
    {
       $this->onpage = $onpage;
    }
    public function getType() : BlockTypeEntity
    {
        return $this->type;
    }
    public function setType(BlockTypeEntity $type)
    {
        $this->type = $type;
    }

    public function getRegions() : PersistentCollection
    {
        return $this->regions;
    }
    public function addRegion(RegionEntity $region)
    {
        if(!$this->regions->contains($region))
        {
           $this->regions->add($region);
        }
    }
    public function removeRegion(RegionEntity $region)
    {
        if($this->regions->contains($region))
        {
            $this->regions->removeElement($region);
        }
    }
    public function getTitle() : string
    {
        return $this->title;
    }
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
    public function getPriority() : int
    {
        return $this->priority;
    }
    public function setPriority(string $priority)
    {
        $this->priority = $priority;
    }

    public function setBlockClass($em, ContainerInterface $container)
    {
        $blockClassName = $this->getType()->getClass() . 'BlockTypeEntity';
        $blockClass = $em->getRepository('\\App\\Entity\\BlockTypes\\' . $blockClassName)->findBy(array('block'=> $this->getId()));
        $blockClass[0]->setContainer($container);
        $this->blockclass = $blockClass[0];
    }
    public function getBlockclass($em, ContainerInterface $container)
    {
        if($this->blockclass != null)
        {
            return $this->blockclass;
        }    
        else
        {
            $this->setBlockClass($em, $container);
            return $this->blockclass;
        }
    }

    public function getListedpagesarray()
    {
        $array = explode(',', $this->listedpages);
        return $array;
    }
     
}
