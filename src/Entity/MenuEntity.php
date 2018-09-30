<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity(repositoryClass="App\Repository\MenuEntityRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class MenuEntity
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
     * @ORM\OneToOne(targetEntity="App\Entity\RoleEntity")
     * @ORM\JoinColumn(name="menu_role", referencedColumnName="id", nullable=true)
     */
    private $role;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MenuItemEntity", mappedBy="menu")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $items;
    // add your own fields
    
    public function __construct() {
        $this->items = new ArrayCollection();
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
    
    public function setRole($role)
    {
        $this->role = $role;
    }
    
    public function getRole()
    {
        return $this->role;
    }
    
    public function getItems()
    {
        return $this->items;
    }
    
    public function addItem($item)
    {
        if(!$this->items->contains($item))
            $this->items->add ($item);
    }
    
    public function removeItem($item)
    {
        if($this->items->contains($item))
            $this->items->removeElement ($item);
    }
    
    public function getItemcount()
    {
        return $this->items->count();
    }
}
