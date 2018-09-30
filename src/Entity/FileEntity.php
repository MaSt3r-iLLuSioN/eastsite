<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Filesystem\Filesystem;
/**
 * @ORM\Entity(repositoryClass="App\Repository\FileEntityRepository")
 */
class FileEntity
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
    private $filename;
    
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $description;
    
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $entity;
    
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $originalname;
    
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $url;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $thumburl = null;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $deleteurl;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $size = null;
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $type;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $slideshowenabled = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasthumb = false;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled = false;
    
    // add your own fields
    public function getId() : int
    {
        return $this->id;
    }
    public function getThumburl()
    {
        return $this->thumburl;
    }
    public function setThumburl(string $url)
    {
        $this->thumburl = $url;
    }
    public function getHasthumb()
    {
        return $this->hasthumb;
    }
    public function setHasthumb($hasThumb)
    {
        $this->hasthumb = $hasThumb;
    }
    public function getSlideshowenabled()
    {
        return $this->slideshowenabled;
    }
    public function setSlideshowenabled($enabled)
    {
        $this->slideshowenabled = $enabled;
    }
    public function getEnabled()
    {
        return $this->enabled;
    }
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
    public function getEntity()
    {
        return $this->entity;
    }
    public function setEntity(string $entity)
    {
        $this->entity = $entity;
    }
    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }
    public function getFilename() : string
    {
        return $this->filename;
    }
    public function setOriginalName(string $originalName)
    {
        $this->originalname = $originalName;
    }
    public function getOriginalName() : string
    {
        return $this->originalname;
    }
    public function setUrl(string $url)
    {
        $this->url = $url;
    }
    public function getUrl() : string
    {
        return $this->url;
    }
    public function setType(string $type)
    {
        $this->type = $type;
    }
    public function getType() : string
    {
        return $this->type;
    }
    public function setSize(string $size)
    {
        $this->size = $size;
    }
    public function getSize()
    {
        return $this->size;
    }
    public function setDeleteurl(string $deleteUrl)
    {
        $this->deleteurl = $deleteUrl;
    }
    public function getDeleteurl():string
    {
        return $this->deleteurl;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
    public function removeFile($uploadDirectory)
    {
        $fs = new Filesystem();
        $fragments = explode('/', $this->getDeleteurl());
        $file = $fragments[1];
        $entity = $fragments[2];
        $folder = $fragments[5];
        $filename = $fragments[6];
        $folderName = '';
        //check to see if only one folder was included or multiple
        if(strpos($folder, '-') !== false)
        {
            //there are multiple folders
            $folders = explode('-', $folder);

            foreach($folders as $fold)
            {
                $folderName .= $fold . '/';
            }
        }
        else
            $folderName = $folder . '/';
        var_dump($uploadDirectory.'/'.$folderName . $filename);
        //exit();
        $fs->remove($uploadDirectory.'/' . $folderName .$filename);
    }
}
