<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\TabRepository")
 */
class Tab
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
    private $title;
    
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $mt;
    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $content;
    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\TabList", inversedBy="tabs")
     * @ORM\JoinColumn(nullable=false)
    */
    private $tab;
    public function getId() : int
    {
        return $this->id;
    }
    public function getMt() : string
    {
        return $this->mt;
    }
    public function setMt(string $mt)
    {
        $this->mt = $mt;
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
    public function getTab() : TabList
    {
        return $this->tab;
    }
    public function setTab(TabList $tab)
    {
        $this->tab = $tab;
    }
}
