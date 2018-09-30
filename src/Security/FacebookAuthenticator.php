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
 * Description of FacebookAuthenticator
 *
 * @author Trey
 */
class FacebookAuthenticator extends SocialAuthenticator {
    //put your code here
    private $clientRegistry;
    private $em;
    private $router;
    private $passwordEncoder;
    private $session;
    private $errorMessage;
    private $securityContext;
    private $firstLinked = false;
    
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router, UserPasswordEncoderInterface $passEncoder, ContainerInterface $c)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->passwordEncoder = $passEncoder;
        $this->session = $c->get('session');
        $this->securityContext = $c->get('security.authorization_checker');
    }
    public function getCredentials(Request $request) {
        if($request->getPathInfo() != '/facebook/connect/check')
            //dont oauth
            return;
        return $this->fetchAccessToken($this->getFacebookClient());
    }
    
    private function getFacebookClient()
    {
        return $this->clientRegistry->getClient('facebook');
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        $facebookUser = $this->getFacebookClient()->fetchUserFromToken($credentials);
        $email = $facebookUser->getEmail();
        if($email == null)
        {
            $this->errorMessage = 'Your email address is not valid on your Facebook Account. Please update your Facebook Account with a correct email address and try again.';
            return null;
        }
        //1 have they logged in with google before? if so log them in!
        $existingUser = $this->em->getRepository('App\Entity\User')->findOneBy(['facebookid'=>$facebookUser->getId()]);
        if($existingUser)
        {
            return $existingUser;
        }        
        //2 do we have a matching user by email?
        $user = $this->em->getRepository('App\Entity\User')->findOneBy(['email'=>$email]);
        if($user)
        {
            //check to see if there google account is linked and if not then link it
            $userFacebbokId = $user->getFacebookid();
            if($userFacebbokId == '' && $userFacebbokId == null)
            {
                //set the users google id, then persist them.
                $user->setFacebookid($facebookUser->getId());
                $user->setFacebooktoken($credentials);
                $user->setFacebookavatar($facebookUser->getPictureUrl());
                $user->setFacebookname($facebookUser->getName());
                $user->setFacebookfirstname($facebookUser->getFirstName());
                $user->setFacebooklastname($facebookUser->getLastName());
                $hometown = $facebookUser->getHometown();
                if($hometown != null)
                    $user->setFacebookhometown($hometown);
                $user->setFacebooklink($facebookUser->getLink());
                $user->setFacebookgender($facebookUser->getGender());
                //$user->setFacebookbio($facebookUser->getBio());
                $user->setFacebookset(true);
                $this->em->persist($user);
                $this->em->flush();
                $this->firstLinked = true;
                return $user;
            }
            return $user;
        }
        
        //3 if no user was found then we should create a account for them.
        $user = new User();
        $user->setUsername($facebookUser->getName());
        $user->setEmail($email);
        $user->setFacebookid($facebookUser->getId());
        $user->setFacebooktoken($credentials);
        $user->setFacebookavatar($facebookUser->getPictureUrl());
        $user->setFacebookname($facebookUser->getName());
        $user->setFacebookfirstname($facebookUser->getFirstName());
        $user->setFacebooklastname($facebookUser->getLastName());
        $hometown = $facebookUser->getHometown();
        if($hometown != null)
            $user->setFacebookhometown($hometown);
        $user->setFacebooklink($facebookUser->getLink());
        $user->setFacebookgender($facebookUser->getGender());
        //$user->setFacebookbio($facebookUser->getBio());
        $user->setFacebookset(true);
        $user->setRole('ROLE_USER');
        $user->setAvatar($facebookUser->getPictureUrl());
        $user->setPlainPassword('testPass');
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
        if($request->getPathInfo() != '/facebook/connect/check')
            //dont oauth
            return false;
        return true;
    }

}
