<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
//use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LikableEntityRepository")
 */
class LikableEntity extends BaseEntity
{
    /**
     *
     * @ORM\Column(type="integer")
     */
    protected $likes = 0;
    
    /**
     *
     * @ORM\Column(type="string")
     */
    protected $type;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="likablecontent")
     * 
     */
    protected $likedusers;
    
    public function __construct()
    {
        $this->likedusers = new ArrayCollection();
    }
    
    public function getLikedusers()
     {
         return $this->likedusers;
     }
     
     public function addLikeduser($user)
     {
         if($this->likedusers->contains($user))
             return;
         $this->likedusers->add($user);
     }
     
     public function removeLikeduser($user)
     {
         if($this->likedusers->contains($user))
             $this->likedusers->removeElement ($user);
     }
     
     public function getLikes()
     {
         return $this->likes;
     }
     public function addLike()
     {
         $this->likes++;
     }
     public function removeLike()
     {
         $this->likes--;
     }
     public function getType()
     {
         return $this->type;
     }
     
     public function setType($type)
     {
         $this->type = $type;
     }
}
