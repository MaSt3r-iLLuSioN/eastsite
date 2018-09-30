<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;
use App\Entity\FileEntity;
use Doctrine\ORM\PersistentCollection;
use getID3;
/**
 * Description of FileUploaderUtility
 *
 * @author Trey
 */
class FileUploaderUtility {
    //put your code here
    public function makeExistingFilesArray($files, string $directory) : array
    {
        $existingFiles = array();
        
        foreach($files as $file)
        {
            if($file instanceof FileEntity)
            {
                $tf = array();
                $tf['type'] = $file->getType();
                $tf['url'] = $file->getUrl();
                $tf['name'] = $file->getOriginalname();
                $tf['id'] = $file->getId();
                $tf['deleteUrl'] = $file->getDeleteurl();
                $tf['description'] = $file->getDescription();
                $tf['hasthumb'] = $file->getHasthumb();
                $tf['entity'] = $file->getEntity();
                if($file->getHasthumb() == true)
                    $tf['thumbnail'] = $file->getThumburl ();
                switch($file->getType())
                {
                    case 'mp3':
                        $tf['deleteString'] = 'MP3 Audio File';
                        $fileName = str_replace('/uploads/', '', $file->getUrl());
                        $fileImage = '';
                        $getID3 = new getID3();
                        $mp3Info = $getID3->analyze($directory . '/' . $fileName);
                        if(isset($mp3Info['comments']['picture'][0]))
                        {
                            //set the image to the mp3 cover -pretty cool stuff right here!!
                            //get image contents from mp3
                            $imgContents = 'data:'.$mp3Info['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($mp3Info['comments']['picture'][0]['data']); 

                            $fileImage = $imgContents;
                        }
                        else
                            $fileImage = '/icons/mp3-logo.png';

                        $tf['imageUrl'] = $fileImage;
                        break;
                    case 'mp4':
                        $tf['deleteString'] = 'MP4 Video File';
                        break;
                    case 'pdf':
                        $tf['deleteString'] = 'PDF File';
                        break;
                    case 'jpg':
                        $tf['deleteString'] = 'JPG File';
                        break;
                    case 'giff':
                        $tf['deleteString'] = 'GIFF Image File';
                        break;
                    case 'png':
                        $tf['deleteString'] = 'PNG Image File';
                        break;
                    default:
                        $tf['deleteString'] = 'File';
                        break;
                }
                $existingFiles[] = $tf;
            }
        }
        return $existingFiles;
    }
}
