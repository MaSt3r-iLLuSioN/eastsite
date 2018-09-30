<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Security;

use App\Entity\User;
use App\Entity\PermissionEntity;
use App\Entity\RoleEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Description of PermissionsVoter
 *
 * @author Trey
 */
class PermissionsVoter extends Voter{
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    //put your code here
    protected function supports($attribute, $subject): bool {
        //attribute variable is the permission ex: (in controller) $this->denyAccessUnlessGranted($attribute, $subject);
        //check to see if the attribute equals a permission entity if so, return true otherwise false
        $permission = $this->em->getRepository(PermissionEntity::class)->findBy(['machinetitle'=>$attribute]);
        if(!is_array($permission))
            return false;
        if(isset($permission[0]) && $permission[0] != null && $permission[0] instanceof PermissionEntity)
            return true;
        return false;
        
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool {
        //grab the permission entity
        $permission = $this->em->getRepository(PermissionEntity::class)->findBy(['machinetitle'=>$attribute]);
        $user = $token->getUser();
        
        if($user == 'anon.')
            return false;
        
        //grab the users roles
        $userRoles = $user->getRolecollection();
        $hasPerm = false;
        //grab each role entity based off of users role(s)
        foreach($userRoles as $role)
        {
            if($role->hasPermission($permission[0]))
                $hasPerm = true;
        }
        return $hasPerm;
    }

}
