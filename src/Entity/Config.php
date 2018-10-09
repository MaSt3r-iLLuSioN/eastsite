<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity(repositoryClass="App\Repository\ConfigRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Config
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $title;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $facebooklink = null;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $googlelink = null;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $twitterlink = null;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $linkedinlink = null;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $instagramlink = null;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $youtubelink = null;
    /**
     * Many entities have Many files.
     * @ORM\ManyToMany(targetEntity="FileEntity")
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="config_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id")}
     *      )
     */
    private $files;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $about;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $home;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $siteurl;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $logo;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $slogan;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $mission;
    /**
     * Many Configs have One Calendar Admin Google User.
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Assert\NotBlank()
     */
    private $googleuser;
    /**
     * Many Configs have One Blog Category Group.
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryGroup")
     * @Assert\NotBlank()
     */
    private $blogcategory;
    /**
     * Many Configs have One Project Category Group.
     * @ORM\ManyToOne(targetEntity="App\Entity\CategoryGroup")
     * @Assert\NotBlank()
     */
    private $projectcategory;
    /**
     * Many base related entities have Many Keywords.
     * @ORM\ManyToMany(targetEntity="KeywordEntity")
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="config_id", referencedColumnName="id")},
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
        $this->files = new ArrayCollection();
        $this->keywords = new ArrayCollection();
    }
    public function getInstagramlink()
    {
        return $this->instagramlink;
    }
    public function setInstagramlink($link)
    {
        $this->instagramlink = $link;
    }
    public function getYoutubelink()
    {
        return $this->youtubelink;
    }
    public function setYoutubelink($link)
    {
        $this->youtubelink = $link;
    }
    public function getLinkedinlink()
    {
        return $this->linkedinlink;
    }
    public function setLinkedinlink($link)
    {
        $this->linkedinlink = $link;
    }
    public function getFacebooklink()
    {
        return $this->facebooklink;
    }
    public function setFacebooklink($link)
    {
        $this->facebooklink = $link;
    }
    public function getGooglelink()
    {
        return $this->googlelink;
    }
    public function setGooglelink($link)
    {
        $this->googlelink = $link;
    }
    public function getTwitterlink()
    {
        return $this->twitterlink;
    }
    public function setTwitterlink($link)
    {
        $this->twitterlink = $link;
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
    public function getKeywords()
    {
        return $this->keywords;
    }
    public function getMetadescription() : string
    {
        return $this->metadescription;
    }
    public function setMetadescription(string $metaDescription)
    {
        $this->metadescription = $metaDescription;
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
    public function getProjectcategory()
    {
        return $this->projectcategory;
    }
    public function setProjectcategory(CategoryGroup $catGroup)
    {
        $this->projectcategory = $catGroup;
    }
    public function getBlogcategory()
    {
        return $this->blogcategory;
    }
    public function setBlogcategory(CategoryGroup $catGrop)
    {
        $this->blogcategory = $catGrop;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
    public function getSiteurl(): string
    {
        return $this->siteurl;
    }
    public function setSiteurl(string $siteurl)
    {
        $this->siteurl = $siteurl;
    }
    public function getId()
    {
        return $this->id;
    }
    public function setGoogleuser(User $user)
    {
        $this->googleuser = $user;
    }
    public function getGoogleuser()
    {
        return $this->googleuser;
    }
    public function getLogo()
    {
        return $this->logo;
    }
    public function setLogo(string $logo)
    {
        $this->logo = $logo;
    }
    public function getAbout()
    {
        return $this->about;
    }
    public function setAbout(string $about)
    {
        $this->about = $about;
    }
    public function getHome()
    {
        return $this->home;
    }
    public function setHome(string $home)
    {
        $this->home = $home;
    }
    public function getSlogan()
    {
        return $this->slogan;
    }
    public function setSlogan(string $slogan)
    {
        $this->slogan = $slogan;
    }
    public function getMission()
    {
        return $this->mission;
    }
    public function setMission(string $mission)
    {
        $this->mission = $mission;
    }
}
