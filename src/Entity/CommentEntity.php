<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentEntityRepository")
 */
class CommentEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
      * Many comments can have one parent
    * @ORM\ManyToOne(targetEntity="App\Entity\CommentEntity", inversedBy="replies")
     * 
    */
     private $parent = null;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CommentEntity", mappedBy="parent")
     */
     private $replies;
    
    /**
     @ORM\Column(type="boolean")
     */
    private $isreply = false;
   
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Assert\NotBlank()
     */
    private $author;
    
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $comment;
    
    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $date;

    public function __construct()
    {
        $this->replies = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setParent(CommentEntity $parent)
    {
        $this->parent = $parent;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
    public function getReplies() : PersistentCollection
    {
        return $this->replies;
    }
    
    public function addReply(CommentEntity $reply)
    {
        if($this->replies->contains($reply))
            return;
        $this->replies->add($reply);
    }
    
    public function removeReply($reply)
    {
        if($this->replies->contains($reply))
            $this->replies->removeElement ($reply);
    }
    
    public function getAuthor()
    {
        return $this->author;
    }
    
    public function setAuthor($author)
    {
        $this->author = $author;
    }
    
    public function getComment()
    {
        return $this->comment;
    }
    
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
    
    public function setIsreply($is)
    {
        $this->isreply = $is;
    }
    
    public function getIsreply()
    {
        return $this->isreply;
    }
}
