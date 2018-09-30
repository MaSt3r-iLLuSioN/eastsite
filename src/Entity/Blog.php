<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity(repositoryClass="App\Repository\BlogRepository")
 */
class Blog extends CommentableEntity
{
    public function __construct()
    {
        parent::__construct();
        $this->type = 'blog';
        $this->files = new ArrayCollection();
    }
        
    /**
     * Many entities have Many files.
     * @ORM\ManyToMany(targetEntity="FileEntity")
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="blog_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id")}
     *      )
     */
    private $files;
    
    public function getFiles()
    {
        return $this->files;
    }
    
    public function addFile(FileEntity $file)
    {
        if(!$this->files->contains($file))
            $this->files->add ($file);
    }
    public function removeFile(FileEntity $file) : bool
    {
        if($this->files->contains($file))
        {
            $this->files->removeElement($file);
            return true;
        }
        return false;
    }
    
    public function hasFile(FileEntity $file) : bool
    {
        if($this->files->contains($file))
            return true;
        return false;
    }
    
    public function setImage(string $image)
    {
        $this->image = $image;
    }
    public function getImage()
    {
        return $this->image;
    }
    
    public function getGenerateurl() : string
    {
        $url = $this->getSystemtitle();
        $url = $this->getCategory()->getSystemtitle() . '/' . $url;
        if($this->getCategory()->getParent() != null)
        {
            $url = $this->recursiveUrl($this->getCategory()->getParent()) . '/' . $url;
        }
        return $url;
    }
    public function recursiveUrl(CategoryEntity $category)
    {
        $url = '';
        $url = $category->getSystemtitle() . $url;
        if($category->getParent() != null)
        {
            $url = $this->recursiveUrl($category->getParent()) . '/' . $url;
        }
        return $url;
        
    }
}
