<?php

namespace App\Controller;

use App\Service\Breadcrumbs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TestimonialController extends Controller
{
    /**
     * @Route("/testimonial", name="testimonial")
     */
    public function index(Breadcrumbs $breadcrumbs)
    {
        // replace this line with your own code!
        return $this->render('@Maker/demoPage.html.twig', [ 'path' => str_replace($this->getParameter('kernel.project_dir').'/', '', __FILE__) ]);
    }
}
