<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Security;
use App\Entity\User;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of GoogleAuthenticator
 *
 * @author Trey
 */
class GoogleAuthenticator extends SocialAuthenticator{
    //put your code here
    private $clientRegistry;
    private $em;
    private $router;
    private $session;
    private $errorMessage;
    private $securityContext;
    private $firstLinked = false;
    
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router, UserPasswordEncoderInterface $passEncoder,ContainerInterface $c)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->session = $c->get('session');
        $this->securityContext = $c->get('security.authorization_checker');
    }
    
    public function getCredentials(Request $request) {
        if($request->getPathInfo() != '/google/connect/check')
            //dont oauth
            return;
        return $this->fetchAccessToken($this->getGoogleClient());
    }
    private function getGoogleClient()
    {
        return $this->clientRegistry->getClient('google');
    }
    public function getUser($credentials, UserProviderInterface $userProvider) {
        //var_dump($credentials);
        //exit();
        $googleUser = $this->getGoogleClient()->fetchUserFromToken($credentials);
        $email = $googleUser->getEmail();
        if($email == null)
        {
            $this->errorMessage = 'Your email address is not valid on your Google Account. Please update your Google Account with a correct email address and try again.';
            return null;
            
        }
        //1 have they logged in with google before? if so log them in!
        $existingUser = $this->em->getRepository('App\Entity\User')->findOneBy(['googleid'=>$googleUser->getId()]);
        if($existingUser)
        {
            return $existingUser;
        }        
        //2 do we have a matching user by email?
        $user = $this->em->getRepository('App\Entity\User')->findOneBy(['email'=>$email]);
        if($user)
        {
            //check to see if there google account is linked and if not then link it
            $userGoogleId = $user->getGoogleid();
            if($userGoogleId == '' && $userGoogleId == null)
            {
                //set the users google id, then persist them.
                $user->setGoogleid($googleUser->getId());
                $user->setGoogletoken($credentials);
                $user->setGoogleavatar($googleUser->getAvatar());
                $user->setGooglename($googleUser->getName());
                $user->setGooglefirstname($googleUser->getFirstName());
                $user->setGooglelastname($googleUser->getLastName());
                $user->setGoogleset(true);
                $this->em->persist($user);
                $this->em->flush();
                $this->firstLinked = true;
                return $user;
            }
            return $user;
        }
        
        //3 if no user was found then we should create a account for them.
        $user = new User();
        $user->setUsername($googleUser->getName());
        $user->setEmail($email);
        $user->setGoogleavatar($googleUser->getAvatar());
        $user->setGoogleid($googleUser->getId());
        $user->setGoogletoken($credentials);
        $user->setGooglename($googleUser->getName());
        $user->setGooglefirstname($googleUser->getFirstName());
        $user->setGooglelastname($googleUser->getLastName());
        $user->setGoogleset(true);
        $user->setRole('ROLE_USER');
        $user->setAvatar($googleUser->getAvatar());
        $password = $user->getPlainPassword();
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        $this->em->persist($user);
        $this->em->flush();
        $this->firstLinked = true;
        return $user;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        $url = '';
        if($this->securityContext->isGranted('ROLE_USER'))
        {
            $url = $this->router->generate('account');
        }
        else
        {
            $url = $this->router->generate('login');
        }
        $this->session->getFlashBag()->add('danger', $this->errorMessage);
        $response = new RedirectResponse($url);
        return $response;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        $url = $this->router->generate('account');
        if($this->firstLinked == true)
            $this->session->getFlashBag()->add('notice', 'Your Google Account has been linked successfully!');
        return new RedirectResponse($url);
    }

    public function start(Request $request, AuthenticationException $authException = null) {
        
    }

    public function supports(Request $request): bool {
        if($request->getPathInfo() != '/google/connect/check')
            //dont oauth
            return false;
        return true;
    }

}
