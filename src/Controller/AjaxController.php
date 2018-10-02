<?php

namespace App\Controller;

use App\Entity\FileEntity;
use App\Entity\User;
use App\Entity\LikableEntity;
use App\Entity\KeywordEntity;
use App\Entity\Blog;
use App\Entity\Project;
use App\Service\FileUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use getID3;
class AjaxController extends Controller
{
    /**
     * @Route("/like/{uid}/{eid}/{type}", name="likeContent")
     */
    public function likeContent(int $uid, int $eid, string $type, Request $request)
    {
        $response = array();
        $content = '';
        $em = $this->getDoctrine()->getManager();
        if($type == 'blog')
            $content = $em->getRepository (Blog::class)->find ($eid);
        elseif($type == 'project')
            $content = $em->getRepository (Project::class)->find($eid);
        if($content instanceof Project || $content instanceof Blog && $this->getUser()->getId() == $uid)
        {
            $countText = '';
            $user = $this->getUser();
            if($user->hasLikableContent($content))
            {
                //remove the content from users likes and degrade like count for content
                $user->removeLikableContent($content);
                $em->persist($user);
                $em->flush();
                $content->removeLike();
                $em->persist($content);
                $em->flush();
                $countText = 'Like';
            }
            else
            {
                $user->addLikableContent($content);
                $em->persist($user);
                $em->flush();
                $content->addLike();
                $em->persist($content);
                $em->flush();
                $countText = 'Unlike';
            }
            $response['success'] = true;
            $response['text'] = $countText;
            $response['count'] = $content->getLikes();
            return new JsonResponse($response);
        }
        
    }
    /**
     * Returns all site keyword entities via json 
     * @Route("/ajax/keywords", name="ajaxKeywords")
     */
    public function ajaxKeywords()
    {
        $em = $this->getDoctrine()->getManager();
        $keywords = $em->getRepository(KeywordEntity::class)->findAll();
        $content = array();
        foreach($keywords as $keyword)
        {
            $content[] = $keyword->getTitle();
        }
        return new JsonResponse($content);
    }
    /**
     * @Route("/file/upload/{entity}/{folder}/{file}", name="fileUploader")
     */
    public function fileUploader(string $entity, string $folder,string $file, Request $request)
    {
        $uploaded_file = $request->files->get('form');
        $description = $request->get('file_description');
        $slideshowEnabled = false;
        if($request->get('file_slideshow_enabled'))
            $slideshowEnabled = $request->get('file_slideshow_enabled');
        if($description == null)
        {
            $fileArray = array(
                'error'=>'Description is required.'
            );
            $returnFile = new \stdClass();
            $returnFiles->files[] = $fileArray;
            return new JsonResponse($returnFiles);
        }
        if ($uploaded_file['postfile']) 
        {
            $fs = new Filesystem();
            $em = $this->getDoctrine()->getManager();
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
            //make the directory if it doesnt exists
            if(!$fs->exists($this->getParameter('upload_directory') . '/' .$folderName))
                $fs->mkdir($this->getParameter('upload_directory') . '/' . $folderName);
            //init new file uploader
            $fileUploader = new FileUploader($this->getParameter('upload_directory') . '/' . $folderName);
            $fileEntity = new FileEntity();
            $fileEntity->setSize($uploaded_file[$file]->getClientSize());
            $fileType = $uploaded_file[$file]->getClientOriginalExtension();
            $fileName = $fileUploader->upload($uploaded_file[$file]);
            
            $fileImage = '';
            
            
            $fileEntity->setOriginalName($uploaded_file[$file]->getClientOriginalName());
            $fileEntity->setFilename($fileName);
            $fileEntity->setUrl('/uploads/'.$folderName . $fileName);
            $fileEntity->setDeleteurl('/file/delete/'.$entity.'/'.$folder . '/' . $fileEntity->getFilename());
            
            $fileEntity->setDescription($description);
            $fileEntity->setSlideshowenabled($slideshowEnabled);
            if($slideshowEnabled)
                $fileEntity->setEnabled (true);
            $fileArray = array(
                'name' => $fileEntity->getOriginalName(),
                'delete_url' => $fileEntity->getDeleteurl(),
                'size' => $fileEntity->getSize(),
                'description'=>$fileEntity->getDescription(),
            );
            
            switch($fileType)
            {
                case 'pdf':
                    $fileEntity->setType('pdf');
                    $fileArray['thumbnailUrl'] = '/uploads/icons/pdf-icon.jpg';
                    $fileArray['type'] = 'PDF File';
                    break;
                case 'mp3':
                    //rename the mp3 (symfony names it to mpog or something -only symfony bug ive ever found!)
                    $fileName = $this->renameFileExtension($folderName . $fileName,'mpga','mp3');
                
                    $fileImage = '';
                    $getID3 = new getID3();
                    $mp3Info = $getID3->analyze($this->getParameter('upload_directory') . '/' . $fileName);
                    if(isset($mp3Info['comments']['picture'][0]))
                    {
                        //set the image to the mp3 cover -pretty cool stuff right here!!
                        //get image contents from mp3
                        $imgContents = 'data:'.$mp3Info['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($mp3Info['comments']['picture'][0]['data']); 
                        
                        $fileImage = $imgContents;
                    }
                    else
                        $fileImage = '/uploads/icons/mp3-logo.png';

                    $fileEntity->setFilename($fileName);
                    $fileEntity->setUrl('/uploads/' . $fileName);
                    $fileEntity->setDeleteurl('/file/delete/' . $fileName);
                    $fileEntity->setType('mp3');
                    $fileArray['thumbnailUrl'] = $fileImage;
                    $fileArray['type'] = 'MP3 File';
                    $fileArray['audio'] = $fileEntity->getUrl();
                    $fileArray['delete_url'] = '/file/delete/'.$entity . '/'. $fileEntity->getFilename();
                    $fileArray['type'] = 'MP3 Audio File';
                    break;
                    
                case 'mp4':
                    $fileEntity->setType('mp4');
                    $fileArray['video'] = $fileEntity->getUrl();
                    $fileArray['type'] = 'MP4 Video File';
                    break;
                
                case 'jpg':
                    $fileEntity->setType('jpg');
                    $fileEntity->setHasthumb(true);
                    $fileArray['thumbnailUrl'] = $fileEntity->getUrl();
                    $explodedThumb = explode('.',$fileEntity->getUrl());
                    $thumbUri = reset($explodedThumb);
                    $fileArray['thumbnail'] = $thumbUri . '_thumb.'. $fileEntity->getType();
                    $fileEntity->setThumburl($thumbUri . '_thumb.'. $fileEntity->getType());
                    $fileArray['type'] = 'JPG Image File';
                        $fileArray['slideshow'] = 'Eligable For Slideshow';
                    break;
                
                case 'png':
                    $fileEntity->setType('png');
                    $fileEntity->setHasthumb(true);
                    $fileArray['thumbnailUrl'] = $fileEntity->getUrl();
                    $explodedThumb = explode('.',$fileEntity->getUrl());
                    $thumbUri = reset($explodedThumb);
                    $fileArray['thumbnail'] = $thumbUri . '_thumb.'. $fileEntity->getType();
                    $fileEntity->setThumburl($thumbUri . '_thumb.'. $fileEntity->getType());
                    $fileArray['type'] = 'PNG Image File';
                        $fileArray['slideshow'] = 'Eligable For Slideshow';
                    break;
                case 'gif':
                    $fileEntity->setType('gif');
                    $fileArray['thumbnailUrl'] = $fileEntity->getUrl();
                    $fileArray['type'] = 'GIF Image File';
                        $fileArray['slideshow'] = 'Eligable For Slideshow';
                    break;
                default:
                    $fileEntity->setType($uploaded_file[$file]->getClientOriginalExtension());
                    $fileArray['type'] = 'File';
                    break;
            }
            
            //save file entity
            $fileEntity->setEntity($entity);
            $em->persist($fileEntity);
            $em->flush();
            
            $oldDeleteUrl = $fileArray['delete_url'];
            $pos = strpos($oldDeleteUrl, '/file/delete/');
            $newDeleteUrl = substr_replace($oldDeleteUrl,'/file/delete/'.$fileEntity->getId() . '/', $pos,13);
            $fileArray['delete_url'] = $newDeleteUrl;
            $fileEntity->setDeleteurl($newDeleteUrl);
            $fileArray['file_id'] = $fileEntity->getId();
            $fileArray['url'] = $fileEntity->getUrl();
            $returnFile = new \stdClass();
            $returnFile->files[] = $fileArray;
            
            //save file entity
            $em->persist($fileEntity);
            $em->flush();
            
            $response = $returnFile;
        }

        else 
            $response= 'error';
        return new JsonResponse($response);
    }
    
    /**
     * @Route("/file/generate/{file}", name="generateThumbnail")
     */
    public function generateThumbnail(FileEntity $file)
    {
        $em = $this->getDoctrine()->getManager();
        $fileUploader = new FileUploader('');
        $img = $this->getParameter('upload_directory') . str_replace('/uploads', '',$file->getUrl());
        if($fileUploader->generateThumbnail($img, 75, 75, 85))
        {
            $file->setHasthumb(true);
            $url = $file->getUrl();
            $explodedurl = explode('.',$url);
            $filename_no_ext = reset($explodedurl);
        
            $file->setThumburl($filename_no_ext. '_thumb' . '.jpg');
            $em->persist($file);
            $em->flush();
            $response = array();
//          $response['url'] = $thumbName;
            $returnFile = new \stdClass();
            $returnFile->success = true;
            $returnFile->url = $file->getThumburl();
        
            return new JsonResponse($returnFile);
        }
    }
    /**
     * @Route("/file/delete/{file}/{entity}/{folder}/{filename}", name="fileUploaderDelete", requirements={"filename"=".+"})
     */
    public function fileUploaderDelete(FileEntity $file, string $entity, string $folder, string $filename)
    {
        $em = $this->getDoctrine()->getManager();
        
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
        
        if($entity == 'blog')
        {
            //get all blog post
            $posts = $em->getRepository(\App\Entity\Blog::class)->findAll();
            foreach($posts as $post)
            {
                //check the files to see if it contains this file, if so remove it and persist the post
                if($post->removeFile($file))
                {
                    $em->persist($post);
                    $em->flush();
                }
            }
        }
        elseif($entity == 'gallery-block')
        {
            //get all gallery blocks
            $galleryBlocks = $em->getRepository(\App\Entity\BlockTypes\GalleryBlockTypeEntity::class)->findAll();
            foreach($galleryBlocks as $block)
            {
                if($block->removeFile($file))
                {
                    $em->persist($block);
                    $em->flush();
                }
            }
        }
        elseif($entity == 'page')
        {
            $pages = $em->getRepository(\App\Entity\PageEntity::class)->findAll();
            foreach($pages as $page)
            {
                if($page->removeFile($file))
                {
                    $em->persist($page);
                    $em->flush();
                }
            }
        }
        elseif($entity == 'project')
        {
            $projects = $em->getRepository(\App\Entity\Project::class)->findAll();
            foreach($projects as $project)
            {
                if($project->removeFile($file))
                {
                    $em->persist($project);
                    $em->flush();
                }
            }
        }
        
        $em->remove($file);
        $em->flush();
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->getParameter('upload_directory').'/' . $folderName .'/'.$filename);
        if($file->getHasthumb() == true)
        {
            $explodedName = explode('.',$file->getFilename());
            $no_ext_fileName = reset($explodedName);
            $fileSystem->remove ($this->getParameter ('upload_directory').'/'.$folderName.'/'.$no_ext_fileName . '_thumb.jpg');
        }
        $response = 'success';
        return new JsonResponse($response);
    }
    
    private function renameFileExtension(string $filename,string $oldExtension, string $newExtension)
    {
        //first remove the old extension from the filename
        $newFileName = str_replace($oldExtension, $newExtension, $filename);
        $fs = new Filesystem();
        $fs->rename($this->getParameter('upload_directory').'/'.$filename, $this->getParameter('upload_directory') .'/'. $newFileName);
        return $newFileName;
    }
    
    private function makeFile($fileName, $fileContent)
    {
        $fs = new Filesystem();
        //make file and dump contents
        $fs->dumpFile($fileName,$fileContent);
    }
    
}
