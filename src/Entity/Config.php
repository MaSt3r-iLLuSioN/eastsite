<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\ConfigRepository")
 * @ORM\Cache(usage="READ_ONLY")
 */
class Config
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    // add your own fields
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
