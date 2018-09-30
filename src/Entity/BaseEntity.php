<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(type="datetime")
     */
    private $datetime = null;
    /**
     @ORM\Column(type="datetime")
     */
    private $revision;
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
     * @ORM\Column(type="string")
     */
    private $keywords = null;
    
    /**
     * @ORM\Column(type="text")
     */
    private $metadescription = null;
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
    public function getKeywords() : string
    {
        return $this->keywords;
    }
    public function setKeywords(string $keywords)
    {
        $this->keywords = $keywords;
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
