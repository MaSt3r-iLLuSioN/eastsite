<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity(repositoryClass="App\Repository\CalendarEntityRepository")
 */
class CalendarEntity
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
    private $description;
    
    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    private $public;
    
    /**
     * @ORM\Column(type="string")
     */
    private $color;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="calendar")
     */
    private $events;
    
    /**
     * @ORM\Column(type="string")
     */
    private $googleid;
    
    public function __construct() {
        $events = new ArrayCollection();
    }
    
    public function getColor() : string
    {
        return str_replace('#', '', $this->color);
    }
    public function getGooglecolor()
    {
        switch($this->color)
        {
            case '#e67399'://pink
                return '23B1365F';
            case '#c244ab'://fuchsia
                return '235C1158';
            case '#ad2d2d'://red
                return '23711616';
            case '#b5515d'://crimson
                return '23691426';
            case '#f2a640'://orange
                return '23BE6D00';
            case '#e6804d'://orange red
                return '23B1440E';
            case '#c7561e'://red orange
                return '23853104';
            case '#d47f1e'://burnt orange
                return '238C500B';
            case '#b56414'://brown orange
                return '23754916';
            case '#bfbf4d'://gold
                return '2388880E';
            case '#e0C240'://goldenrod
                return '23AB8B00';
            case '#d1bc36'://darker goldenrod
                return '23856508';
            case '#65ad89'://pale green
                return '2328754E';
            case '#59bfb3'://lighter green
                return '231B887A';
            case '#65ad89'://green
                return '2328754E';
            case '#4cb052'://forest green
                return '230D7813';
            case '#8cbf40'://olive green
                return '23528800';
            case '#3c995b'://jungle green
                return '23125A12';
            case '#7ec225'://another olive
                return '232F6309';
            case '#41a587'://sea green
                return '230F4B38';
            case '#668cd9'://standard blue
                return '232952A3';
            case '#94a2be'://blue steel
                return '235A6986';
            case '#3640ad'://dark blue
                return '23060D5E';
            case '#4585a3'://sea blue
                return '23113F47';
            case '#b373b3'://violet
                return '237A367A';
            case '#8c66d9'://purple
                return '235229A3';
            case '#603f99'://deep purple
                return '2323164E';
            case '#ab2671'://magenta
                return '235B123B';
            case '#c4a883'://brown
                return '238D6F47';
            case '#000000'://black
                return '23333333';
        }
    }
    
    public function setColor(string $color)
    {
        $this->color = $color;
    }
    
    public function getPublic(): bool
    {
        return $this->public;
    }
    
    public function setPublic(bool $public)
    {
        $this->public = $public;
    }
    
    public function getEvents()
    {
        return $this->events;
    }
    
    public function addEvent(Event $event)
    {
        if(!$this->events->contains($event))
        {
            $this->events->add($event);
        }
    }
    
    public function removeEvent(Event $event)
    {
        if($this->events->contains($event))
        {
            $this->events->removeElement($event);
        }
    }
    
    public function getId()
    {
        return $this->id;
    }
    public function getGoogleid()
    {
        return $this->googleid;
    }
    public function setGoogleid(string $googleid)
    {
        $this->googleid = $googleid;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
    public function getTitle() : string
    {
        return $this->title;
    }
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
}
