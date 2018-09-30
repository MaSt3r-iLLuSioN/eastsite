<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BlockTypeEntityRepository")
 * @ORM\Cache(usage="READ_ONLY")
 */
class BlockTypeEntity
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
    private $class;

    /**
     * @ORM\Column(type="string")
     */
    private $template;
    
    /**
     * @ORM\Column(type="string")
     */
    private $formtemplate;
    
    // add your own fields
     
     public function getId()
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
     
     public function getClass() : string
     {
         return $this->class;
     }
     
     public function setClass(string $class)
     {
         $this->class = $class;
     }
     
     public function getTemplate() : string
     {
         return $this->template;
     }
     
     public function setTemplate(string $template)
     {
         $this->template = $template;
     }
     
     public function getFormTemplate() : string
     {
         return $this->formtemplate;
     }
     public function setFormTemplate(string $formtemplate)
     {
         $this->formtemplate = $formtemplate;
     }
}
