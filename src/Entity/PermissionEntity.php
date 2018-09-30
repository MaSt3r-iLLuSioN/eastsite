<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PermissionEntityRepository")
 */
class PermissionEntity
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
     * @ORM\Column(type="boolean")
     */
     private $system;
     
     /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
     private $machinetitle;
     
     /**
     * @ORM\Column(type="string")
     */
     private $description;
     
     /**
      * Many permissions can have many roles
    * @ORM\ManyToMany(targetEntity="App\Entity\RoleEntity", mappedBy="permissions")
    */
     private $roles;
     
     /**
     * Many Permissions have One category.
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryEntity")
     */
    private $category;
     
     public function __construct()
     {
         $this->roles = new ArrayCollection();
     }
     
     public function getId() : int
     {
         return $this->id;
     }
     
     public function getTitle() : string
     {
         return $this->title;
     }
     
     public function setTitle(string $title)
     {
         $this->title = $title;
     }
     
     public function getMachinetitle() : string
     {
         return $this->machinetitle;
     }
     
     public function setMachinetitle(string $title)
     {
         $this->machinetitle = $title;
     }
     
     public function getRoles() : PersistentCollection
     {
         return $this->roles;
     }
     
     public function addRole(RoleEntity $role)
     {
         if(!$this->roles->contains($role))
             $this->roles->add($role);
     }
     
     //you would call the role entity remove permission not this, it is just here for test reasons, all good
     public function removeRole(RoleEntity $role)
     {
         if($this->roles->contains($role))
             $this->roles->removeElement($role);
     }
     
     public function getSystem() : bool
     {
         return $this->system;
     }
     
     public function setSystem(bool $system)
     {
         $this->system = $system;
     }
     
     public function getCategory() : CategoryEntity
     {
         return $this->category;
     }
     
     public function setCategory(CategoryEntity $category)
     {
         $this->category = $category;
     }
     
     public function getDescription() : string
     {
         return $this->description;
     }
     
     public function setDescription(string $description)
     {
         $this->description = $description;
     }
}
