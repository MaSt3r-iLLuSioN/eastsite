<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;
use Imagick;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
/**
 * Description of FileUploader
 *
 * @author Trey
 */
class FileUploader
{
    private $targetDirectory;
    private $optimizerChain;
    private $jpegoptim;
    private $pngquant;
    public function __construct($targetDir)
    {
        $this->targetDirectory = $targetDir;
        $this->jpegoptim = new Jpegoptim([
            '--strip-all',
            '--all-progressive',
            '-m85'
        ]);
        //$this->jpegoptim->setBinaryPath('C:\ProgramData\jpegoptim');
        $this->pngquant = new Pngquant([
           '--force', 
        ]);
        //$this->pngquant->setBinaryPath('C:\ProgramData\pngquant');
        $this->optimizerChain = (new OptimizerChain)
            ->addOptimizer($this->jpegoptim)

            ->addOptimizer($this->pngquant);
    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        $file->move($this->getTargetDirectory(), $fileName);
        $logger = new Logger('testing');
        $logger->pushHandler(new StreamHandler($this->targetDirectory.'test.log',Logger::WARNING));
        $this->optimizerChain
                ->useLogger($logger)
                ->optimize($this->targetDirectory . $fileName);
        $this->generateThumbnail($this->targetDirectory . $fileName,75,75);
        return $fileName;
    }
    
    public function generateThumbnail($img, $width, $height, $quality = 85)
    {
        if (is_file($img)) {
            $imagick = new Imagick(realpath($img));
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
            $imagick->setImageCompressionQuality($quality);
            $imagick->thumbnailImage($width, $height, true, false);
            $explodedThumb = explode('.',$img);
            if(!isset($explodedThumb[2]))
                $filename_no_ext = reset($explodedThumb);
            else
            {
                $filename_no_ext = $explodedThumb[0] .'.'. $explodedThumb[1];
            }
            //var_dump($filename_no_ext);
            //exit();
            if (file_put_contents($filename_no_ext . '_thumb' . '.jpg', $imagick) === false) {
                throw new Exception("Could not put contents.");
            }
            return true;
        }
        else {
            throw new Exception("No valid image provided with {$img}.");
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}