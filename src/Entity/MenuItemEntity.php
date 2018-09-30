<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MenuItemEntityRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class MenuItemEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $title;
    
    /**
     * Many categories can have one parent
     * @ORM\ManyToOne(targetEntity="App\Entity\MenuItemEntity", inversedBy="children")
     * @ORM\JoinColumn(name="menu_item_parent", referencedColumnName="id", nullable=true)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
     private $parent;
     /**
     * @ORM\Column(type="integer")
     */
     private $priority = 0;
    
     /**
      * @ORM\ManyToOne(targetEntity="App\Entity\CategoryGroup")
      * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true, unique=false)
      * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
      */
     private $categorygroup = null;
     
     /**
     * @ORM\Column(type="boolean")
     */
     private $hascategory = 0;
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MenuItemEntity", mappedBy="parent")
     * @ORM\OrderBy({"title" = "ASC"})
     */
     private $children;
    
    /**
     * @ORM\Column(type="string",nullable=true)
     */
    private $url;
    
    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    private $system;
    
    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\MenuEntity", inversedBy="items")
    */
    private $menu;

    // add your own fields
    
    public function __construct() {
        $this->children = new ArrayCollection();
    }
    public function getHascategory()
    {
        return $this->hascategory;
    }
    public function setHascategory($hasCategory)
    {
        $this->hascategory = $hasCategory;
    }
    public function getCategorygroup()
    {
        return $this->categorygroup;
    }
    
    public function setCategoryGroup($categorygroup)
    {
        $this->categorygroup = $categorygroup;
    }
    
    public function getPriority()
    {
        return $this->priority;
    }
    
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }
    
    public function getSystem()
    {
        return $this->system;
    }
    
    public function setSystem($system)
    {
        $this->system = $system;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
    
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    
    public function getChildren()
    {
        return $this->children;
    }
    
    public function addChild($child)
    {
        if(!$this->children->contains($child))
            $this->children->add ($child);
    }
    
    public function removeChild($child)
    {
        if($this->children->contains($child))
            $this->children->removeElement ($child);
    }
    
    public function getUrl()
    {
        return $this->url;
    }
    
    public function setUrl($url)
    {
        $this->url = $url;
    }
    
    public function getMenu() : MenuEntity
    {
        return $this->menu;
    }
    
    public function setMenu(MenuEntity $menu)
    {
        $this->menu = $menu;
    }
    
    public function getChildrencount()
    {
        return $this->children->count();
    }
}
