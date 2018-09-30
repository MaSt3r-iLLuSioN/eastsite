<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentableEntityRepository")
 */
class CommentableEntity extends LikableEntity
{
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\CommentEntity")
     * @ORM\JoinTable("entity_comments"))
     */
    protected $comments;
    
    public function __construct() {
        parent::__construct();
        $this->comments = new ArrayCollection();
    }
    
    public function getComments()
    {
        return $this->comments;
    }
    
    public function addComment(CommentEntity $comment)
    {
        if($this->comments->contains($comment))
            return;
        $this->comments->add($comment);
    }
    
    public function removeComment(CommentEntity $comment)
    {
        if($this->comments->contains($comment))
            $this->comments->removeElement ($comment);
    }
    
    public function getCommentcount()
    {
        return $this->comments->count();
    }
}
