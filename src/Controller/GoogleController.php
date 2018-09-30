<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class GoogleController extends Controller
{
    /*
     * @Route("/google/connect", name="google_connect")
     */
    public function googleConnect()
    {
        //Link to this controller to start the "connect" process
        //will redirect to google
        return $this->get('oauth2.registry')
                ->getClient('google')
                ->redirect();
    }
    
    /*
     * @Route("/google/unlink", name="googleUnlink")
     */
    public function googleUnlink()
    {
        $user = $this->getUser();
        $user->setGoogleid('');
        $user->setGoogleavatar('');
        $user->setGooglename('');
        $user->setGooglefirstname('');
        $user->setGooglelastname('');
        $user->setGoogleset(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $this->addFlash('notice', 'Your Google Account has been Un-Linked Successfully!');
        return $this->redirect('/account');
    }
    
    /* 
     * @Route("/google/connect/check", name="google_connect_check")
     */
    public function googleConnectCheck()
    {
        /*
     * After going to google, you're redirected back here 
     * because this is the "redirect_route" you configured
     * in services.yml
     */
        
    }
}
