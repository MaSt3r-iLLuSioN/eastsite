<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Form\Type;
use App\Service\PrettyPhoto;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig_Environment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
/**
 * Description of JQueryFileUploaderType
 *
 * @author Trey
 */
class JQueryFileUploaderType extends AbstractType{
    //put your code here
    private $twig;
    private $dispatcher;
    private $uploadUrl;
    private $hiddenFieldName;
    private $existingFiles;
    private $prettyPhoto;
    private $maxNumberOfFiles;
    public function __construct() {
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'twig'=>null,
            'dispatcher'=>null,
            'id'=>null,
            'uploadUrl'=>null,
            'hiddenFieldName'=>null,
            'existingFiles'=>null,
            'PrettyPhoto'=>null,
            'fileTypes'=>null,
            'maxNumberOfFiles'=>0,
        ));
    }
    
    public function getBlockPrefix() {
        return 'jqueryuploader';
        //parent::getBlockPrefix();
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $this->twig = $options['twig'];
        $this->dispatcher = $options['dispatcher'];
        $this->uploadUrl = $options['uploadUrl'];
        $this->hiddenFieldName = $options['hiddenFieldName'];
        $this->existingFiles = $options['existingFiles'];
        $this->prettyPhoto = $options['PrettyPhoto'];
        $this->maxNumberOfFiles = $options['maxNumberOfFiles'];
        parent::buildForm($builder, $options);
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options) {$hasPrettyPhoto = false;
        $prettyPhotoInit = '';
        if($this->prettyPhoto instanceof PrettyPhoto)
        {
            $this->prettyPhoto->initPrettyPhoto();
            $hasPrettyPhoto = true;
            $prettyPhotoInit = function()
            {
                $this->prettyPhoto->initPrettyPhoto();
            };
        }
        
        if($this->twig instanceof Twig_Environment && $this->dispatcher instanceof EventDispatcherInterface)
        {
            $javascriptContent = $this->twig->render('form/type/JQueryUploader/main.js.html.twig', array(
                'id'=>$options['id'],
                'files'=>$options['existingFiles'],
                'uploadUrl'=>$options['uploadUrl'],
                'hiddenFieldName'=>$options['hiddenFieldName'],
                'hasPrettyPhoto'=>$hasPrettyPhoto,
                'prettyPhoto'=>$this->prettyPhoto,
                'fileTypes'=>$options['fileTypes'],
                'maxNumberOfFiles'=>$this->maxNumberOfFiles
            ));
            $cssContent = $this->twig->render('form/type/JQueryUploader/main.css.html.twig',array());

            $this->dispatcher->addListener('kernel.response', function($event) use ($javascriptContent, $cssContent) {
                $response = $event->getResponse();
                $content = $response->getContent();
                // finding position of </body> tag to add content before the end of the tag
                $pos = strripos($content, '</body>');
                $content = substr($content, 0, $pos).$javascriptContent.substr($content, $pos);

                $pos = strripos($content, '</head>');
                $content = substr($content, 0, $pos).$cssContent.substr($content, $pos);

                $response->setContent($content);
                $event->setResponse($response);
            });
        }
        
        parent::buildView($view, $form, $options);
        $view->vars = array_merge($view->vars, array(
            'existingFiles' => $options['existingFiles'],
            'hasPrettyPhoto'=>$hasPrettyPhoto,
            'prettyPhotoInit'=>$prettyPhotoInit,
            'fileTypes'=>$options['fileTypes'],
            'maxNumberOfFiles'=>$options['maxNumberOfFiles']
        ));
    }

    public function getParent()
    {
        return FileType::class;
    }
}
