<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Google_Client;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email already taken")
 * @UniqueEntity(fields="username", message="Username already taken")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $username;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registerdate;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastonline = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstname = null;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastname = null;
    /**
     * @ORM\Column(type="string", length=255, unique=false)
     */
    private $role;
    
    /**
      * Many users can have many roles
    * @ORM\ManyToMany(targetEntity="App\Entity\RoleEntity", inversedBy="users")
    * @ORM\JoinTable("user_roles") 
    * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
    */
     private $rolecollection;
     
     /**
      * Many users can have many likable content
      * @ORM\ManyToMany(targetEntity="App\Entity\LikableEntity", inversedBy="likedusers")
      * @ORM\JoinTable(name="user_likes")
      */
     private $likablecontent;
     
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=true)
     */
    private $facebookid = null;
    
    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $facebooktoken = null;
    
    /**
     * @ORM\Column(type="boolean", nullable=true, unique=false)
     */
    private $facebookset = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $facebookavatar = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $facebookname = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $facebookfirstname = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $facebooklastname = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $facebooklink = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $facebookbio = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $facebookhometown = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $facebookgender = null;
    /**
     * @ORM\Column(type="boolean", nullable=true, unique=false)
     */
    private $twitterset = null;
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=true)
     */
    private $googleid = null;
    
    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $googletoken = null;
    
    /**
     * @ORM\Column(type="boolean", nullable=true, unique=false)
     */
    private $googleset = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $googleavatar = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $googlename = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $googlefirstname = null;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $googlelastname = null;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * The below length depends on the "algorithm" you use for encoding
     * the password, but this works well with bcrypt.
     *
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", unique=false)
     */
    private $avatar;
    
    /**
     * @ORM\Column(type="string", nullable=true, length=255, unique=false)
     */
    private $bio = null;
    
    /**
     * Many users have one file (picture).
     * @ORM\ManyToMany(targetEntity="FileEntity")
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="file_id", referencedColumnName="id")}
     *      )
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private $files;
    
    public function __construct()
     {
         $this->rolecollection = new ArrayCollection();
         $this->likablecontent = new ArrayCollection();
        $this->files = new ArrayCollection();
     }
     public function getFiles()
    {
        return $this->files;
    }
    
    public function addFile(FileEntity $file)
    {
        if(!$this->files->contains($file))
            $this->files->add ($file);
    }
    public function removeFile(FileEntity $file) : bool
    {
        if($this->files->contains($file))
        {
            $this->files->removeElement($file);
            return true;
        }
        return false;
    }
     public function setTwitterset($set)
     {
         $this->twitterset = $set;
     }
     public function getTwitterset()
     {
         return $this->twitterset;
     }
     public function getLikablecontent()
     {
        return $this->likablecontent;
     }
     public function setBio($bio)
     {
         $this->bio = $bio;
     }
     public function getBio()
     {
         return $this->bio;
     }
     public function getRegisterdate() : \DateTime
     {
         return $this->registerdate;
     }
     public function setRegisterdate(\DateTime $date)
     {
         $this->registerdate = $date;
     }
     public function getLastonline() : \DateTime
     {
         return $this->lastonline;
     }
     public function setLastonline(\DateTime $date)
     {
         $this->lastonline = $date;
     }
     public function setFirstname($firstname)
     {
         $this->firstname = $firstname;
     }
     public function setLastname($lastname)
     {
         $this->lastname = $lastname;
     }
     public function hasLikableContent($content) : bool
     {
         if($this->likablecontent->contains($content))
             return true;
         return false;
     }
     
     public function addLikableContent($entity)
     {
         if($this->likablecontent->contains($entity))
             return;
         $this->likablecontent->add($entity);
     }
     
     public function removeLikableContent($entity)
     {
         if($this->likablecontent->contains($entity))
             $this->likablecontent->removeElement ($entity);
     }
     
     public function getRolecollection() : PersistentCollection
     {
         return $this->rolecollection;
     }
     public function addRole(RoleEntity $role)
     {
         if($this->rolecollection->contains($role))
             return;
         $this->rolecollection->add($role);
         $this->setRole($role->getMachinetitle());
     }
     public function removeRole(RoleEntity $role)
     {
         if($this->rolecollection->contains($role))
             $this->rolecollection->removeElement ($role);
     }
    public function setGoogletoken($token)
    {
        $this->googletoken = $token;
    }
    public function getGoogletoken() 
    {
        return $this->googletoken;
    }
    public function setFacebookid(string $id)
    {
        $this->facebookid = $id;
    }
    public function setFacebooktoken($token)    
    {
        $this->facebooktoken = $token;
    }
    public function getFacebooktoken() 
    {
        return $this->facebooktoken;
    }
    public function getFacebookid() 
    {
        return $this->facebookid;
    }
    public function setFacebookname(string $name)
    {
        $this->facebookname = $name;
    }
    public function getFacebookname() 
    {
        return $this->facebookname;
    }
    public function setFacebookfirstname(string $name)
    {
        $this->facebookfirstname = $name;
    }
    public function getFacebookfirstname()
    {
        return $this->facebookfirstname;
    }
    public function setFacebooklastname(string $name)
    {
        $this->facebooklastname = $name;
    }
    public function getFacebooklastname() 
    {
        return $this->facebooklastname;
    }
    public function setFacebookset(bool $set)
    {
        $this->facebookset = $set;
    }
    public function getFacebookset() 
    {
        return $this->facebookset;
    }
    public function setFacebookavatar(string $avatar)
    {
        $this->facebookavatar = $avatar;
    }
    public function getFacebookavatar()
    {
        return $this->facebookavatar;
    }
    public function setFacebooklink(string $link)
    {
        $this->facebooklink = $link;
    }
    public function getFacebooklink()
    {
        return $this->facebooklink;
    }
    public function setFacebookbio(string $bio)
    {
        $this->facebookbio = $bio;
    }
    public function getFacebookbio()
    {
        return $this->facebookbio;
    }
    public function setFacebookhometown(string $hometown)
    {
        $this->facebookhometown = $hometown;
    }
    public function getFacebookhometown()
    {
        return $this->facebookhometown;
    }
    public function setFacebookgender(string $gender)
    {
        $this->facebookgender = $gender;
    }
    public function getFacebookgender()
    {
        return $this->facebookgender;
    }
    public function getGooglename()
    {
        return $this->googlename;
    }
    public function setGooglename(string $name)
    {
        $this->googlename = $name;
    }
    public function getGooglefirstname()
    {
        return $this->googlefirstname;
    }
    public function setGooglefirstname(string $name)
    {
        $this->googlefirstname = $name;
    }
    public function getGooglelastname()
    {
        return $this->googlelastname;
    }
    public function setGooglelastname(string $name)
    {
        $this->googlelastname = $name;
    }
    public function getGoogleset()
    {
        return $this->googleset;
    }
    public function setGoogleset(bool $googleSet)
    {
        $this->googleset = $googleSet;
    }
    public function getGoogleavatar()
    {
        return $this->googleavatar;
    }
    public function setGoogleavatar(string $googleAvatar)
    {
        $this->googleavatar = $googleAvatar;
    }
    public function getGoogleid()
    {
        return $this->googleid;
    }
    public function setGoogleid(string $googleId)
    {
        $this->googleid = $googleId;
    }
    public function getAvatar()
    {
        return $this->avatar;
    }
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPlainPassword() 
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getSalt()
    {
        // The bcrypt and argon2i algorithms don't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.
        return null;
    }

    public function eraseCredentials() {
        
    }

    public function setRole($roles)
    {
        $this->role .= $roles . ' ';       
    }
    public function getRole()
    {
        return $this->role;
    }
    public function hasRole(RoleEntity $role)
    {
        if($this->rolecollection->contains($role))
            return true;
        return false;
    }
    
    public function hasRoleTitle(string $title)
    {
        foreach($this->rolecollection->toArray() as $role)
        {
            if($role->getTitle() == $title)
                return true;
        }
        return false;
    }
    
    public function getRoles() {
        $r = array();
        foreach($this->rolecollection->toArray() as $role)
        {
            $r[] = $role->getMachinetitle();
        }
        return $r;
    }

    public function serialize() {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    public function unserialize($serialized) {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }
    
    public function getGoogleClient(Container $container, string $redirectUri, Request $request, array $scopes)
    {
        //instantiate a new google client
        $client = new Google_Client();
        //setup the app name
        $client->setApplicationName($container->getParameter('google_app_name'));
        //set the client secret
        $client->setClientSecret($container->getParameter('google_client_secret'));
        //set the client id
        $client->setClientId($container->getParameter('google_client_id'));
        //set the redirect uri (has to also be set in google dev)
        $client->setRedirectUri($redirectUri);
        //set access type -offline for token access
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        //iterate through each scope
        foreach($scopes as $scope)
        {
            //add scope to client
            $client->addScope($scope);
        }
        //path to where this user token is saved
        $tokenPath = '/var/www/eastwaycustomhomes.com/html/oauth/google/tokens/'.$this->getId();
        //var_dump($tokenPath);
        //check token path to see if it exists and if so the load the token from it
        if(file_exists($tokenPath))
        {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
        }
        else
        {
            //we have to request a token for this user because there is none in the file system
            //if code param isnt in url then request auth code from google
            if(!$request->query->get('code'))
            {
                $authUrl = $client->createAuthUrl();
                header('Location: ' . $authUrl);
                //exit php so we will be redirected 
                exit();
            }
            //grab the auth code from the url and use it to get token
            $code = $request->query->get('code');
            $accessToken = $client->fetchAccessTokenWithAuthCode($code);
            
            //if token doesnt exist in filesystem then make it (saves token)
            if(!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            //write the token to file
            file_put_contents($tokenPath, json_encode($accessToken));
        }
        //set the access token to google client
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            //get new token from client refresh token
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            //save new token to file
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }
    
    function expandHomeDirectory($path) {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }

}
