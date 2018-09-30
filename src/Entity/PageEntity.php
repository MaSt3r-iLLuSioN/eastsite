<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity(repositoryClass="App\Repository\PageEntityRepository")
 */
class PageEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * Many posts have Many files.
     * @ORM\ManyToMany(targetEntity="FileEntity")
     * @ORM\JoinTable(name="page_files",
     *      joinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id")}
     *      )
     */
    private $files;
    
    /**
     * @ORM\ManyToMany(targetEntity="PageEntity")
     * @ORM\JoinTable(name="page_pages",
     *      joinColumns={@ORM\JoinColumn(name="parent_page_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id")}
     *      )
     */
    private $pages;
    
    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\LayoutEntity", inversedBy="pages")
     * @ORM\JoinColumn(nullable=false)
    */
     private $layout;
     
     /**
      * @ORM\OneToOne(targetEntity="App\Entity\MenuItemEntity")
      * @ORM\JoinColumn(name="page_menu", referencedColumnName="id", nullable=true)
      */
     private $menu;
    
    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     */
     private $title;
     
     /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     */
     private $url;
     
     /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
     private $content;
     
     /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
     private $keywords;
     
     /**
     * @ORM\Column(type="boolean")
     */
     private $showsubpages = false;
    
     /**
     * @ORM\Column(type="boolean")
     */
     private $hasmenu = false;
     
     /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
     private $metadescription;

     public function __construct()
     {
         $this->files = new ArrayCollection();
         $this->pages = new ArrayCollection();
     }
     
     public function getId()
     {
         return $this->id;
     }
     
     public function getHasmenu()
     {
         return $this->hasmenu;
     }
     
     public function setHasmenu($hasMenu)
     {
         $this->hasmenu = $hasMenu;
     }
     
     public function getShowsubpages()
     {
         return $this->showsubpages;
     }
     
     public function setShowsubpages($show)
     {
         $this->showsubpages = $show;
     }
     public function getFiles()
    {
        return $this->files;
    }
    
    public function addPage(PageEntity $page)
    {
        if(!$this->pages->contains($page))
            $this->pages->add($page);
    }
    
    public function getFilecount()
    {
        return $this->files->count();
    }
    
    public function getPages()
    {
        return $this->pages;
    }
    
    public function removePage(PageEntity $page)
    {
        if($this->pages->contains($page))
        {
            $this->pages->removeElement ($page);
            return true;
        }
        return false;
    }
    
    public function hasPage($page)
    {
        if($this->pages->contains($page))
            return true;
        return false;
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
    public function hasFile(FileEntity $file)
    {
        if($this->files->contains($file))
        {
            return true;
        }
        return false;
    }
     public function getMenu()
     {
         return $this->menu;
     }
     public function setMenu($menu)
     {
         $this->menu = $menu;
     }
     public function getKeywords() : string
     {
         return $this->keywords;
     }
     public function setKeywords(string $keywords)
     {
         $this->keywords = $keywords;
     }
     public function getUrl() : string
     {
         return $this->url;
     }
     public function setUrl(string $url)
     {
         $this->url = $url;
     }
     public function getTitle() : string
     {
         return $this->title;
     }
     public function setTitle(string $title)
     {
         $this->title = $title;
     }
     public function getContent() : string
     {
         return $this->content;
     }
     public function setContent(string $content)
     {
         $this->content = $content;
     }
     public function getContentshort()
    {
        $preContent = strip_tags($this->content);
        if (strlen($preContent) > 300)
            $preContent = substr($preContent, 0, 297) . '...';
        return $preContent;
    }
     public function getLayout() : LayoutEntity
     {
         return $this->layout;
     }
     public function setLayout(LayoutEntity $layout)
     {
         $this->layout = $layout;
     }
     public function getMetadescription()
     {
         return $this->metadescription;
     }
     public function setMetadescription($metaDescription)
     {
         $this->metadescription = $metaDescription;
     }
    // add your own fields
}
