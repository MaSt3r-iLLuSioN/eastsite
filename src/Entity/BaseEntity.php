<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity(repositoryClass="App\Repository\BaseEntityRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"baseentity" = "BaseEntity", "Blog" = "Blog", "Project" = "Project","LikableEntity" = "LikableEntity","CommentableEntity" = "CommentableEntity"})
 */
class BaseEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    
    // add your own fields
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $title = null;
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $systemtitle = null;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datetime = null;
    /**
     @ORM\Column(type="datetime", nullable=true)
     */
    private $revision = null;
    /**
     * All Eniities have One Author.
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $author = null;
    
    /**
     * @ORM\Column(type="text")
     */
    private $content = null;
    
    
    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryEntity")
     */
    private $category = null;
    
    /**
     * Many base related entities have Many Keywords.
     * @ORM\ManyToMany(targetEntity="KeywordEntity")
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="node_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="keyword_id", referencedColumnName="id")}
     *      )
     */
    private $keywords;
    
    /**
     * @ORM\Column(type="text")
     */
    private $metadescription = null;
    
    public function __construct()
    {
        $this->keywords = new ArrayCollection();
    }
    
    public function getKeywords()
    {
        return $this->keywords;
    }
    
    public function addKeyword(KeywordEntity $keyword)
    {
        if(!$this->keywords->contains($keyword))
            $this->keywords->add ($keyword);
    }
    public function removeKeyword(KeywordEntity $keyword) : bool
    {
        if($this->keywords->contains($keyword))
        {
            $this->keywords->removeElement($keyword);
            return true;
        }
        return false;
    }
    
    public function resetKeywords()
    {
        $this->keywords = new ArrayCollection();
    }
    
    public function hasKeyword(KeywordEntity $keyword) : bool
    {
        if($this->keywords->contains($keyword))
            return true;
        return false;
    }
    
    public function getRevision()
    {
        return $this->revision;
    }
    public function setRevision()
    {
        $this->revision = new \DateTime();
    }
    public function computeETag()
    {
        return md5($this->getRevision());
    }
    public function getSystemtitle() : string
    {
        return $this->systemtitle;
    }
    public function setSystemtitle(string $systemTitle)
    {
        $this->systemtitle = $systemTitle;
    }
    public function getMetadescription() : string
    {
        return $this->metadescription;
    }
    public function setMetadescription(string $metaDescription)
    {
        $this->metadescription = $metaDescription;
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
    public function getContentshort()
    {
        $preContent = strip_tags($this->content);
        if (strlen($preContent) > 300)
            $preContent = substr($preContent, 0, 297) . '...';
        return $preContent;
    }
    public function setContent(string $content)
    {
        $content = str_replace('<blockquote>', '<blockquote class="blockquote">', $content);
        $this->content = $content;
    }
    public function getUrl()
    {
        $catUrl = $this->category->getUrl();
        $url = $catUrl . '/' . $this->systemtitle;
        return $url;
    }
    public function setCategory(CategoryEntity $category)
    {
        $this->category = $category;
    }
    public function getCategory() : CategoryEntity
    {
        return $this->category;
    }
    public function getAuthor() : User
    {
        return $this->author;
    }
    public function setAuthor(User $author)
    {
        $this->author = $author;
    }
    public function getDatetime() : \DateTime
    {
        return $this->datetime;
    }
    public function setDatetime(\DateTime $date)
    {
        $this->datetime = $date;
    }
    
    public function getId()
    {
        return $this->id;
    }
}
