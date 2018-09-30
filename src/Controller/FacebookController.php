<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FacebookController extends Controller
{
    /*
     * @Route("/facebook/connect", name="facebook_connect")
     */
    public function facebookConnect()
    {
        //Link to this controller to start the "connect" process
        //will redirect to google
        return $this->get('oauth2.registry')
                ->getClient('facebook')
                ->redirect();
    }
    
    /*
     * @Route("/facebook/unlink", name="facebookUnlink")
     */
    public function facebookUnlink()
    {
        $user = $this->getUser();
        $user->setFacebookid('');
        $user->setFacebookavatar('');
        $user->setFacebookname('');
        $user->setFacebookfirstname('');
        $user->setFacebooklastname('');
        $user->setFacebookhometown('');
        $user->setFacebooklink('');
        $user->setFacebookgender('');
        $user->setFacebookbio('');
        $user->setFacebookset(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $this->addFlash('notice', 'Your Facebook Account has been Un-Linked Successfully!');
        return $this->redirect('/account');
    }
    
    /* 
     * @Route("/facebook/connect/check", name="facebook_connect_check")
     */
    public function facebookConnectCheck()
    {
        /*
     * After going to google, you're redirected back here 
     * because this is the "redirect_route" you configured
     * in services.yml
     */
        
    }
}
