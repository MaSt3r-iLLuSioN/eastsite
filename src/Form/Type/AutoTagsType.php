<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Form\Type;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig_Environment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AutoTagsType extends AbstractType
{
    private $dispatcher;
    private $dataUrl;
    public function __construct() {
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'twig'=>null,
            'dispatcher'=>null,
            'id'=>null,
            'dataUrl'=>null,
        ));
    }
    public function getBlockPrefix() {
        return 'autotags';
        //parent::getBlockPrefix();
    }
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $this->twig = $options['twig'];
        $this->dispatcher = $options['dispatcher'];
        $this->dataUrl = $options['dataUrl'];
        parent::buildForm($builder, $options);
    }
    public function buildView(FormView $view, FormInterface $form, array $options) 
    {
        if($this->twig instanceof Twig_Environment && $this->dispatcher instanceof EventDispatcherInterface)
        {
            $javascriptContent = $this->twig->render('form/type/AutoTags/main.js.html.twig', array(
                'id'=>$options['id'],
                'dataUrl'=>$options['dataUrl']
            ));
            $cssContent = $this->twig->render('form/type/AutoTags/main.css.html.twig',array());

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
            
        ));
    }
    public function getParent()
    {
        return TextType::class;
    }
}
