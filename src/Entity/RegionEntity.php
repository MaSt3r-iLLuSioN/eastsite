<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\RegionEntityRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class RegionEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="integer", unique=false)
     * @Assert\NotBlank()
     */
    private $priority;
    
    /**
     * Many Regions have many Layouts
     * @ORM\ManyToMany(targetEntity="App\Entity\LayoutEntity", mappedBy="regions")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
     private $layouts;
     
     /**
     * @ORM\ManyToMany(targetEntity="App\Entity\BlockEntity", inversedBy="regions")
     * @ORM\JoinTable("region_blocks") 
     * @ORM\OrderBy({"priority" = "ASC"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
     private $blocks;
     
     public function __construct()
     {
         $this->layouts = new ArrayCollection();
         $this->blocks = new ArrayCollection();
     }
     
     public function getId() : int
     {
         return $this->id;
     }
     
     public function getPriority() : int
    {
        return $this->priority;
    }
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }
     
     public function getBlocks() : PersistentCollection
     {
         return $this->blocks;
     }
     
     public function addBlock(BlockEntity $block)
     {
         if(!$this->blocks->contains($block))
            $this->blocks->add($block);
     }
     
     public function removeBlock(BlockEntity $block)
     {
         if($this->blocks->contains($block))
            $this->blocks->removeElement($block);
         return;
     }
     
     public function getLayouts() : PersistentCollection
     {
         return $this->layouts;
     }
     
     public function addLayout(LayoutEntity $layout)
     {
         if(!$this->layouts->contains($layout))
            $this->layouts->add($layout);
     }
     
     public function removeLayout(LayoutEntity $layout)
     {
         if($this->layouts->contains($layout))
             $this->layouts->removeElement ($layout);
     }
     
     public function getTitle() : string
     {
         return $this->title;
     }
     
     public function setTitle(string $title)
     {
         $this->title = $title;
     }
}
