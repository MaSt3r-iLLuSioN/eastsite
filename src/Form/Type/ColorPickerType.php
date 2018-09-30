<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Form\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
/**
 * Description of ColorPickerType
 *
 * @author Trey
 */
class ColorPickerType extends AbstractType{
    //put your code here
    private $twig;
    private $dispatcher;
    
    public function __construct() {
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'twig'=>null,
            'dispatcher'=>null,
            'id'=>null,
            'colors'=>array(
                'E67399',//pink
                'C244AB',//Fuchsia 
                'AD2D2D',//Red 
                'B5515D',//Crimson 
                'F2A640',//Orange 
                'E6804D',//Orange Red
                'C7561E',//Red Orange
                'D47F1E',//Burnt Orange
                'B56414',//Brown Orange 
                'BFBF4D',//Gold 
                'E0C240',//Goldenrod
                'D1BC36',//Darker Goldenrod
                '65AD89',//Pale Green
                '59BFB3',//Lighter Green
                '65AD89',//Green 
                '4CB052',//Forest Green
                '8CBF40',//Olive Green
                '3C995B',//Jungle Green
                '7EC225',//Another Olive
                '41A587',//Sea Green
                '668CD9',//Standard Blue
                '94A2BE',//Blue Steel 
                '3640AD',//Dark Blue
                '4585A3',//Sea Blue
                'B373B3',//Violet
                '8C66D9',//Purple
                '603F99',//Deep Purple
                'AB2671',//Magenta 
                'C4A883',//Brown 
                '000000',//Black
                
            ),
            'showHexField'=>false,
            'pickerDefault'=>null,
        ));
    }
    
    public function getBlockPrefix() {
        return 'colorpicker';
        //parent::getBlockPrefix();
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $this->twig = $options['twig'];
        $this->dispatcher = $options['dispatcher'];
        parent::buildForm($builder, $options);
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options) {
        //parent::buildView($view, $form, $options);
        $javascriptContent = $this->twig->render('form/type/ColorPicker/main.js.twig', array(
            'id'=>$options['id'], 
            'colors'=>$options['colors'],
            'showHexField'=>$options['showHexField'],
            'pickerDefault'=>$options['pickerDefault'],
        ));
        $cssContent = $this->twig->render('form/type/ColorPicker/main.css.twig',array());

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
        parent::buildView($view, $form, $options);
    }
       
   


    public function getParent()
    {
        return TextType::class;
    }
}
