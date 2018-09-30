<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

use App\Service\Breadcrumbs;
/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryEntityRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class CategoryEntity
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
     * @ORM\Column(type="string")
     */
    private $url;
    /**
     * @ORM\Column(type="string")
     */
    private $systemtitle;

    /**
     * @ORM\Column(type="boolean")
     */
    private $system;
    /**
      * Many categories can have one parent
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
    * @ORM\ManyToOne(targetEntity="App\Entity\CategoryEntity", inversedBy="children")
    */
     private $parent = null;
    
    /**
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     * @ORM\OneToMany(targetEntity="App\Entity\CategoryEntity", mappedBy="parent")
     * @ORM\OrderBy({"title" = "ASC"})
     */
     private $children;
    /**
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
    * @ORM\ManyToOne(targetEntity="App\Entity\CategoryGroup", inversedBy="categories")
     * @ORM\JoinColumn(nullable=false)
    */
    private $categorygroup;
    
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
    // add your own fields
    public function getId() : int
    {
        return $this->id;
    }
    
    public function getSystemtitle() : string
    {
        return $this->systemtitle;
    }
    public function setSystemtitle(string $systemTitle)
    {
        $this->systemtitle = $systemTitle;
    }
    public function setSystem(bool $system)
    {
        $this->system = $system;
    }
    public function getSystem() : bool
    {
        return $this->system;
    }
    public function addChild(CategoryEntity $child) : bool
    {
        if(!$this->children->contains($child))
        {
            $this->children->add($child);
            return true;
        }
        return false;
    }
    public function removeChild(CategoryEntity $child) : bool
    {
        if($this->children->contains($child))
        {
            $this->children->removeElement($child);
            return true;
        }
        return false;
    }
    public function getChildren() : PersistentCollection
    {
        return $this->children;
    }
    public function getChildrencount()
    {
        return $this->children->count();
    }
    public function addParent(CategoryEntity $parent)
    {
        $this->parent = $parent;
    }
    public function removeParent()
    {
        $this->parent = null;
    }
    
    public function getParent()
    {
        return $this->parent;
    }

    public function getTitle() : string
    {
        return $this->title;
    }
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
    public function getCategorygroup() : CategoryGroup
    {
        return $this->categorygroup;
    }
    public function setCategorygroup(CategoryGroup $categorygroup)
    {
        $this->categorygroup = $categorygroup;
    }
    public function getGenerateurl() : string
    {
        $url = $this->getSystemtitle();
        if($this->getParent() != null)
        {
            $url = $this->recursiveUrl($this->getParent()) . '/' . $url;
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
    public function getUrl() : string
    {
        return $this->url;
    }
    public function setUrl(string $url)
    {
        $this->url = $url;
    }
    public function handleBreadcrumbs(Breadcrumbs $breadcrumbs, string $url, CategoryEntity $category)
    {
        $breadcrumbArray = $this->buildRecursiveCategoryBreadcrumbArray($category, $url);
        $breadcrumbArrayCount = count($breadcrumbArray) - 1;
        for($i = $breadcrumbArrayCount; $i >= 0; $i--)
        {
            $breadcrumbs->addBreadcrumb($breadcrumbArray[$i]['title'], $breadcrumbArray[$i]['url']);
        }
    }
    private function buildRecursiveCategoryBreadcrumbArray($category,string $url,$var = null)
    {
        if($var == null)
            $var = array();
        $var[] = array(
            'title'=>$category->getTitle(),
            'url'=> '/'.$url.'/'. $this->buildRecursiveCategoryUrl($category)
        );
        if($category->getParent() != null)
            $var = $this->buildRecursiveCategoryBreadcrumbArray($category->getParent(),$url, $var);
        return $var;
    }
    private function buildRecursiveCategoryUrl(CategoryEntity $category)
    {
        $url = '';
        if($category->getParent() != null)
        {
            $url .= $this->buildRecursiveCategoryUrl($category->getParent()) . '/' . $category->getSystemtitle();
        }
        else
        {
            $url .= $category->getSystemtitle();
        }
        return $url;
    }
}
