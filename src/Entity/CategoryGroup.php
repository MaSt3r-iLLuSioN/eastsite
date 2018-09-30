<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryGroupRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class CategoryGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $system;
    
    /**
     * @ORM\Column(type="string")
     */
    private $title;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $url = null;
    
    /**
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     * @ORM\OneToMany(targetEntity="App\Entity\CategoryEntity", mappedBy="categorygroup")
     * @ORM\OrderBy({"title" = "ASC"})
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }
    
    public function getUrl() : string
    {
        return $this->url;
    }
    
    public function setUsr(string $url)
    {
        $this->url = $url;
    }
    
    public function getId()
    {
        return $this->id;
    }
    public function setSystem(bool $system)
    {
        $this->system = $system;
    }
    public function getCategorycount()
    {
        return $this->categories->count();
    }
    public function getSystem() : bool
    {
        return $this->system;
    }
    public function getTitle() : string
    {
        return $this->title;
    }
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
    public function getCategories() : PersistentCollection
    {
        return $this->categories;
    }
    // add your own fields
}
