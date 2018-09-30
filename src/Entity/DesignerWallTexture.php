<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DesignerWallTextureRepository")
 */
class DesignerWallTexture
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $scale;

    /**
     * @ORM\Column(type="boolean")
     */
    private $stretch;
    
    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryEntity")
     */
    private $category = null;
    
    /**
     * Many entities have Many files.
     * @ORM\ManyToMany(targetEntity="FileEntity")
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="texture_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id")}
     *      )
     */
    private $files;
    
    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCategory(CategoryEntity $category)
    {
        $this->category = $category;
    }
    public function getCategory() : CategoryEntity
    {
        return $this->category;
    }
    
    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    public function getScale()
    {
        return $this->scale;
    }

    public function setScale(string $scale)
    {
        $this->scale = $scale;

        return $this;
    }

    public function getStretch()
    {
        return $this->stretch;
    }

    public function setStretch(bool $stretch)
    {
        $this->stretch = $stretch;

        return $this;
    }
    
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
}
