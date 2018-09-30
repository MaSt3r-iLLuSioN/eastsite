<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
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
    private $description;
    
    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\CalendarEntity", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
    */
    private $calendar;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User")
     * @ORM\JoinTable(name="event_attendees",
     *      joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    private $attendees;
    
    
     /**
     * @ORM\Column(type="string",nullable=true)
     */
    private $additionalattendees;
    
    /**
     * @ORM\Column(type="string")
     */
    private $location;
    
    /**
     * @ORM\Column(type="string")
     */
    private $googleid;
    
    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $datestart;
    
    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $dateend;
    
    public function __construct() {
        $this->attendees = new ArrayCollection();
    }
    
    public function getGoogleid()
    {
        return $this->googleid;
    }
    
    public function setGoogleid(string $googleid)
    {
        $this->googleid = $googleid;
    }
    
    public function getLocation()
    {
        return $this->location;
    }
    
    public function setLocation(string $location)
    {
        $this->location = $location;
    }
    
    public function getAdditionalattendees()
    {
        return $this->additionalattendees;
    }
    
    public function setAdditionalAttendees( $additionalattendees)
    {
        $this->additionalattendees = $additionalattendees;
    }
    
    public function getAttendees()
    {
        return $this->attendees;
    }
    
    public function addAttendee(User $attendee)
    {
        if(!$this->attendees->contains($attendee))
        {
            $this->attendees->add($attendee);
        }
    }
    
    public function removeAttendee(User $attendee)
    {
        if($this->attendees->contains($attendee))
        {
            $this->attendees->removeElement($attendee);
        }
    }
    public function getId()
    {
        return $this->id;
    }
    public function getCalendar() : CalendarEntity
    {
        return $this->calendar;
    }
    public function setCalendar(CalendarEntity $calendar)
    {
        $this->calendar = $calendar;
    }
    public function getTitle() : string
    {
        return $this->title;
    }
    public function setTitle(string $title)
    {
        $this->title = $title;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
    public function getDatestart() : \DateTime
    {
        return $this->datestart;
    }
    public function setDatestart(\DateTime $date)
    {
        $this->datestart = $date;
    }
    public function getDateend() : \DateTime
    {
        return $this->dateend;
    }
    public function setDateend(\DateTime $date)
    {
        $this->dateend = $date;
    }
    
}
