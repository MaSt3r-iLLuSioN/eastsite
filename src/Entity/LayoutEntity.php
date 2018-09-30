<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\LayoutEntityRepository")
 */
class LayoutEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    // add your own fields
    /**
     * Many Layouts have many Regions
     * @ORM\ManyToMany(targetEntity="App\Entity\RegionEntity", inversedBy="layouts")
     * @ORM\JoinTable("layout_regions") 
     */
    private $regions;
    
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $type;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PageEntity", mappedBy="layout")
     */
    private $pages;

    
    public function __construct() {
        $this->regions = new ArrayCollection();
    }
    public function getId()
    {
        return $this->id;
    }
    
    public function getRegions() : PersistentCollection
    {
        return $this->regions;
    }
    public function addRegion(RegionEntity $region)
    {
        $this->regions[] = $region;
    }
    public function getType() : string
    {
        return $this->type;
    }
    public function setType(string $type)
    {
        $this->type = $type;
    }
    
}
