<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoleEntityRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class RoleEntity
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
     * Many Roles has One Category Group.
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryGroup")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $categorygroup;
     
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\PermissionEntity", inversedBy="roles")
     * @ORM\JoinTable("role_permissions") 
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
     private $permissions;
     
     /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="rolecollection")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
     private $users;

     public function __construct()
     {
         $this->permissions = new ArrayCollection();
         $this->users = new ArrayCollection();
     }
     
     public function getId() : int
     {
         return $this->id;
     }
     
     public function setTitle(string $title)
     {
         $this->title = $title;
     }
     
     public function getTitle() : string
     {
         return $this->title;
     }
     
     public function getMachinetitle(): string
     {
         return $this->machinetitle;
     }
     
     public function setMachinetitle(string $machineTitle)
     {
         $this->machinetitle = $machineTitle;
     }
     
     public function hasPermission(PermissionEntity $permission)
     {
         if($this->permissions->contains($permission))
             return true;
         return false;
     }
     public function hasPermissionTitle(string $title)
     {
         foreach($this->permissions as $permission)
         {
             if($permission->getTitle() == $title)
             {
                 return true;
             }
         }
         return false;
     }
     
     public function getUsers() : PersistentCollection
     {
         return $this->users;
     }
     
     public function getPermissions() : PersistentCollection
     {
        return $this->permissions;
     }
     
     public function addPermission(PermissionEntity $permission)
     {
        // add your own fields
         if(!$this->permissions->contains($permission))
             $this->permissions->add($permission);
     }
     
     public function removePermission(PermissionEntity $permission)
     {
         if($this->permissions->contains($permission))
             $this->permissions->removeElement ($permission);
     }
     
     public function getSystem() : bool
     {
         return $this->system;
     }
     
     public function setSystem(bool $system)
     {
         $this->system = $system;
     }
     
     public function setCategorygroup(CategoryGroup $group)
     {
         $this->categorygroup = $group;
     }
     
     public function getCategorygroup() : CategoryGroup
     {
         return $this->categorygroup;
     }
     
     public function roleExists(string $machineName, \Doctrine\ORM\EntityManagerInterface $em)
     {
         if($machineName == 'ADMIN_ROLE' || $machineName == 'USER_ROLE' || $machineName == 'ROLE_ADMIN' || $machineName == 'ROLE_USER')
             return true;
         $role = $em->getRepository(RoleEntity::class)->findBy(['machinetitle'=>$machineName]);
         if(isset($role[0]) && $role[0]->getMachinetitle() == $machineName )
             return true;
         return false;
         
     }
}
