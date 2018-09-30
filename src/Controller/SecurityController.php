<?php

namespace App\Controller;

use App\Service\Breadcrumbs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use App\Form\UserType;
use App\Entity\User;
class SecurityController extends BaseController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authUtils,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->setActive('User Login');
        $breadcrumbs->setBreadcrumbs();
        //get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();
        
        //last username entered by user
        $lastUsername = $authUtils->getLastUsername();
        
        //setup the form
        $session = $request->getSession();
        
        $csrfGenerator = new UriSafeTokenGenerator();
        $csrfStorgage = new SessionTokenStorage($session);
        $csrfManager = new CsrfTokenManager($csrfGenerator, $csrfStorgage);
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->addExtension(new CsrfExtension($csrfManager))
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('_username', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Your Username or Email',
                        'class' => 'form-control',
                        'name' => '_username'
                    )
                ))
                ->add('_password', PasswordType::class, array(
                    'attr' => array(
                        'placeholder' => 'Your Password',
                        'class' => 'form-control',
                        'name' => '_password'
                    )
                ))
                ->getForm();
        
        return $this->render('security/login.html.twig', array(
           'last_username' => $lastUsername,
            'error' => $error,
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder,Breadcrumbs $breadcrumbs)
    {
        parent::hideProfiler($this->getUser());
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->setActive('User Registration');
        $breadcrumbs->setBreadcrumbs();
        //1. build form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        
        //2. handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            //3. encode the password
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $em = $this->getDoctrine()->getManager();
            $role = $em->getRepository(\App\Entity\RoleEntity::class)->findBy(['machinetitle'=>'ROLE_USER']);
            $user->addRole($role[0]);
            //$user->setRole('ROLE_ADMIN');
            $user->setAvatar('default.jpg');
            //4. save the user
            $em->persist($user);
            $em->flush();
            $this->addFlash('notice', 'You have registered successfully!');
            //maybe send email etc
            return $this->redirectToRoute('login');
        }
        
        return $this->render('security/register.html.twig', array(
            'form'=>$form->createView(),
        ));
    }
}
