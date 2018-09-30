<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;
use Twig_Environment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
/**
 * Description of PrettyPhoto
 *
 * @author Trey
 */
class PrettyPhoto {
    //put your code here
    private $twig;
    private $eventDispatcher;
    
    //private $files;
    
    //prettyhpoto options
    public $animationSpeed = 'fast';
    public $slideshowSpeed = 5000;
    public $autoplay_slideshow = 'false';
    public $opacity = 0.8;
    public $show_title = 'true';
    public $allow_resize = 'true';
    public $default_width = 500;
    public $default_height = 344;
    public $counter_seperator_label = '/';
    public $theme = 'dark_rounded';
    public $horizontal_padding = 20;
    public $hideflash = 'true';
    public $wmode = 'opaque';
    public $autoplay = 'true';
    public $modal = 'false';
    public $deeplinking = 'true';
    public $overlay_gallery = 'true';
    public $keyboard_shortcuts = 'true';
    public $ie6_fallback = 'true';
    public $markup;
    public $gallery_markup;
    public $image_markup;
    public $flash_markup;
    public $quicktime_markup;
    public $ifram_markup;
    public $inline_markup;
    public $custom_markup;
    public $social_tools;
    
    public function __construct(Twig_Environment $twig, EventDispatcherInterface $eventDispatcher) {
        $this->twig = $twig;
        $this->eventDispatcher = $eventDispatcher;
        $this->markup = '<div class="pp_pic_holder"> \
                            <div class="ppt">&nbsp;</div> \
                                <div class="pp_top"> \
                                        <div class="pp_left"></div> \
                                        <div class="pp_middle"></div> \
                                        <div class="pp_right"></div> \
                                </div> \
                                <div class="pp_content_container"> \
                                        <div class="pp_left"> \
                                        <div class="pp_right"> \
                                                <div class="pp_content"> \
                                                        <div class="pp_loaderIcon"></div> \
                                                        <div class="pp_fade"> \
                                                                <a href="#" class="pp_expand" title="Expand the image">Expand</a> \
                                                                <div class="pp_hoverContainer"> \
                                                                        <a class="pp_next" href="#">next</a> \
                                                                        <a class="pp_previous" href="#">previous</a> \
                                                                </div> \
                                                                <div id="pp_full_res"></div> \
                                                                <div class="pp_details"> \
                                                                        <div class="pp_nav"> \
                                                                                <a href="#" class="pp_arrow_previous">Previous</a> \
                                                                                <p class="currentTextHolder">0/0</p> \
                                                                                <a href="#" class="pp_arrow_next">Next</a> \
                                                                        </div> \
                                                                        <p class="pp_description"></p> \
                                                                        {pp_social} \
                                                                        <a class="pp_close" href="#">Close</a> \
                                                                </div> \
                                                        </div> \
                                                </div> \
                                        </div> \
                                        </div> \
                                </div> \
                                <div class="pp_bottom"> \
                                        <div class="pp_left"></div> \
                                        <div class="pp_middle"></div> \
                                        <div class="pp_right"></div> \
                                </div> \
                            </div> \
                        <div class="pp_overlay"></div>';
        $this->gallery_markup = '<div class="pp_gallery"> \
                                    <a href="#" class="pp_arrow_previous">Previous</a> \
                                    <div> \
                                            <ul> \
                                                    {gallery} \
                                            </ul> \
                                    </div> \
                                    <a href="#" class="pp_arrow_next">Next</a> \
                            </div>';
        $this->image_markup = '<img id="fullResImage" src="{path}" />';
        $this->flash_markup = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{width}" height="{height}"><param name="wmode" value="{wmode}" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="{path}" /><embed src="{path}" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="{width}" height="{height}" wmode="{wmode}"></embed></object>';
        $this->quicktime_markup = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" height="{height}" width="{width}"><param name="src" value="{path}"><param name="autoplay" value="{autoplay}"><param name="type" value="video/quicktime"><embed src="{path}" height="{height}" width="{width}" autoplay="{autoplay}" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/"></embed></object>';
	$this->ifram_markup = '<iframe src ="{path}" width="{width}" height="{height}" frameborder="no"></iframe>';
        $this->inline_markup = '<div class="pp_inline">{content}</div>';
        $this->custom_markup = '';
        $this->social_tools = '<div class="pp_social"><div class="twitter"><a href="http://twitter.com/share" class="twitter-share-button" data-count="none">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script></div><div class="facebook"><iframe src="http://www.facebook.com/plugins/like.php?locale=en_US&href=&amp;layout=button_count&amp;show_faces=true&amp;width=500&amp;action=like&amp;font&amp;colorscheme=light&amp;height=23" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:500px; height:23px;" allowTransparency="true"></iframe></div></div>';
    }
    public function addFiles($files)
    {
        $this->files = $files;
    }
    public function initPrettyPhoto()
    {
        $javascriptContent = $this->twig->render('libraries/prettyphoto/main.js.html.twig', array(
            'animation_speed'=>$this->animationSpeed,
            'slideshow'=>$this->slideshowSpeed,
            'autoplay_slideshow'=>$this->autoplay_slideshow,
            'opacity'=>$this->opacity,
            'show_title'=>$this->show_title,
            'allow_resize'=>$this->allow_resize,
            'default_width'=>$this->default_width,
            'default_height'=>$this->default_height,
            'counter_separator_label'=>$this->counter_seperator_label,
            'theme'=>$this->theme,
            'horizontal_padding'=>$this->horizontal_padding,
            'hideflash'=>$this->hideflash,
            'wmode'=>$this->wmode,
            'autoplay'=>$this->autoplay,
            'modal'=>$this->modal,
            'deeplinking'=>$this->deeplinking,
            'overlay_gallery'=>$this->overlay_gallery,
            'keyboard_shortcuts'=>$this->keyboard_shortcuts,
            'ie6_fallback'=>$this->ie6_fallback,
            'markup'=>$this->markup,
            'gallery_markup'=>$this->gallery_markup,
            'image_markup'=>$this->image_markup,
            'flash_markup'=>$this->flash_markup,
            'quicktime_markup'=>$this->quicktime_markup,
            'iframe_markup'=>$this->ifram_markup,
            'inline_markup'=>$this->inline_markup,
            'custom_markup'=>$this->custom_markup,
            'social_tools'=>$this->social_tools
        ));
        $cssContent = $this->twig->render('libraries/prettyphoto/main.css.html.twig',array());

        $this->eventDispatcher->addListener('kernel.response', function($event) use ($javascriptContent, $cssContent) {
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
    public function createView()
    {
        $this->initPrettyPhoto();
        //$slideshow = $this->twig->render('libraries/prettyphoto/slideshow.html.twig',array('files'=>$this->files));
        //return $slideshow;
    }
}
