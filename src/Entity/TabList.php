<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="App\Repository\TabListRepository")
 */
class TabList
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
     * @ORM\OneToMany(targetEntity="App\Entity\Tab", mappedBy="tab")
     */
    private $tabs;
    
    public function __construct()
    {
        $this->tabs = new ArrayCollection();
    }
    public function getId() : int
    {
        return $this->id;
    }
    public function getTitle() : string
    {
        return $this->title;
    }
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
    
    /**
     * @return Collection|Tab[]
     */
    public function getTabs() : PersistentCollection
    {
        return $this->tabs;
    }
}
