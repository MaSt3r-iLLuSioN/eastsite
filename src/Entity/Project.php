<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project extends LikableEntity
{
    
    /**
     * Many entities have Many files.
     * @ORM\ManyToMany(targetEntity="FileEntity")
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id")}
     *      )
     */
    private $files;
    /**
    * @ORM\Column(type="string", nullable=true)
    */
    private $location;
    /**
    * @ORM\Column(type="string", nullable=true)
    */
    private $latitude;
    /**
    * @ORM\Column(type="string", nullable=true)
    */
    private $longitude;
    /**
    * @ORM\Column(type="string", nullable=true)
    */
    private $style;
    /**
    * @ORM\Column(type="integer", nullable=true)
    */
    private $status;
    /**
    * @ORM\Column(type="integer", nullable=true)
    */
    private $stage;
     
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
    public function __construct()
    {
        parent::__construct();
        $this->files = new ArrayCollection();
        $this->type = 'project';
    }
    
    public function getLatitude()
    {
        return $this->latitude;
    }
    
    public function setLatitude(string $latitude)
    {
        $this->latitude = $latitude;
    }
    
    public function getLongitude()
    {
        return $this->longitude;
    }
    public function setLongitude(string $longitude)
    {
        $this->longitude = $longitude;
    }
    
    public function getStyle()
    {
        return $this->style;
    }
    
    public function setStyle(string $style)
    {
        $this->style = $style;
    }
    
    public function getLocation()
    {
        return $this->location;
    }
    
    public function setLocation(string $location)
    {
        $this->location = $location;
    }
    
    public function getStage()
    {
        return $this->stage;
    }
    
    public function setStage(int $stage)
    {
        $this->stage = $stage;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatus(int $status)
    {
        $this->status = $status;
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
