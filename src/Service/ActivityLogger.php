<?php

namespace App\Service;

use App\Entity\ActivityLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ActivityLogger{
    private $em;
    private $security;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
    ){
        $this->em = $em;
        $this->security = $security;
    }

    public function log(string $action, ?string $targetData = null){
        $user = $this-> security->getUser();

        $log = new ActivityLog();
        $log ->setAction($action);
        $log ->setTargetData($targetData);
        $log ->setDatetime(new \DateTimeImmutable());
        
        if($user){
            $log->setUserId($user);
            $log->setUsername($user->getUserIdentifier());
            $log->setRole(implode(',', $user->getRoles()));
        }

        $this->em->persist($log);
        $this->em->flush();
    }
}