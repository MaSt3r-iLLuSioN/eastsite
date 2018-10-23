<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;
use App\Entity\KeywordEntity;
use Doctrine\ORM\EntityManagerInterface;
/**
 * Description of KeywordHelper
 *
 * @author Trey
 */
class KeywordHelper {
    private $em;
    public function __construct(EntityManagerInterface $em) 
    {
        $this->em = $em;
    }
    public function keywordExists($title)
    {
        $keyword = $this->em->getRepository(KeywordEntity::class)->findOneBy(array('title' => $title));
        if($keyword != null && $keyword instanceof KeywordEntity)
        {
            return true;
        }
        return false;
    }
    public function addNewKeywords($keywordString)
    {
        $keywords = explode(',',$keywordString);
        foreach($keywords as $keyword)
        {
            if($keyword != '' && $keyword != null && $keyword != ',')
            {
                //make sure keyword doesnt already exists
                if (!$this->keywordExists($keyword))
                {
                    //add the new keyword
                    $keywordEntity = new KeywordEntity();
                    $keywordEntity->setTitle($keyword);
                    $this->em->persist($keywordEntity);
                    $this->em->flush();
                }
            }
        }
    }
    public function makeKeywordString($keywords)
    {
        $keywordString = '';
        $i = 0;
        foreach($keywords as $keyword)
        {
            if ($i == 0)
                $keywordString = $keyword->getTitle();
            else
                $keywordString .= ','.$keyword->getTitle();
            $i++;
        }
        return $keywordString;
    }
    public function getKeywordsByTitle($keywordstring)
    {
        $keywords = explode(',',$keywordstring);
        $keywordEntities = array();
        foreach($keywords as $keyword)
        {
            if($keyword != '' && $keyword != null && $keyword != ',')
            {
                $keywordEntity = $this->em->getRepository(KeywordEntity::class)->findOneBy(array('title' => $keyword));
                $keywordEntities[] = $keywordEntity;
            }
        }
        return $keywordEntities;
    }
}
