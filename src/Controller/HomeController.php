<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HomeController
 *
 * @author M@St3r_iLLuSioN
 */
namespace App\Controller;
use App\Service\Breadcrumbs;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use \Swift_Mailer;
use \Swift_Message;
use \Symfony\Component\HttpFoundation\Request;

class HomeController extends BaseController{
    //put your code here
    /**
    * @Route("/", name="home")
    */
    function home(Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->setActive('Home', '/');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        
        //get all files that are eligable for slideshow
        $files = $em->getRepository(\App\Entity\FileEntity::class)->findBy(['slideshowenabled'=>true]);
        return $this->render('home/index.html.twig', array(
            'slideshowFiles' => $files,
        ));
    }
    /**
     * @Route("/contact", name="contact")
     */
    function contact(Swift_Mailer $mailer, Request $request,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->setActive('Contact Us');
        $breadcrumbs->setBreadcrumbs();
        $session = $request->getSession();
        
        $username = null;
        $email = null;
        if($this->getUser() != null)
        {
            $username = $this->getUser()->getUsername();
            $email = $this->getUser()->getEmail();
        }
        
        //create form
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();
        $form = $formFactory->createBuilder()
            ->add('name', TextType::class, array(
                'data'=>$username,
                'attr' => array(
                    'placeholder' => 'Your Name',
                    'class' => 'form-control'
                )
            ))
            ->add('email', EmailType::class, array(
                'data'=>$email,
                'attr' => array(
                    'placeholder' => 'Your Email',
                    'class' => 'form-control'
                )
            ))
            ->add('subject', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Message Subject',
                    'class' => 'form-control'
                )
            ))
            ->add('message', TextareaType::class, array(
                'attr' => array(
                    'placeholder' => 'Your Message',
                    'class' => 'form-control'
                )
            ))
            ->getForm();
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $email = $form->get('email')->getData();
            $name = $form->get('name')->getData();
            $subject = $form->get('subject')->getData();
            $message = $form->get('message')->getData();
            
            //send a email to the user thanking them for contacting us
            $emailToSend = (new Swift_Message('Thanks for contacting Eastway Custom Homes!'))
                ->setFrom('teds@eastwaycustomhomes.com')
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        // templates/emails/registration.html.twig
                        'emails/thanks-contact-us.html.twig',
                        array(
                            'message'=>$message,
                            'name'=>$name,
                            'subject'=>$subject
                        )
                    ),
                    'text/html'
                );

            $mailer->send($emailToSend);
            
            //send a email to site admin with the persons email, name, subject, and message
            $emailToSend1 = (new \Swift_Message('Someone has contacted you from the website!'))
                ->setFrom($email)
                ->setTo('teds@eastwaycustomhomes.com')
                ->setBody(
                    'From: ' . $email . '<br />Name: '.$name.'<br />Subject: '.$subject.'<br />Message: ' .$message,
                    'text/html'
                );

            $mailer->send($emailToSend1);
            
            //redirect to home page with notice of email sendt
            $this->addFlash('notice', 'Thank you for contacting us! Your message has been sent to us successfully!');
            return $this->redirectToRoute('home');
            
        }
        return $this->render('pages/contact.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
