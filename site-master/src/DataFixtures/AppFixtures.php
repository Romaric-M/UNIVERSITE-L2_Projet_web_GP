<?php

namespace App\DataFixtures;

use App\Entity\Annonce;
use App\Entity\Forum;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder){
        $this->passwordEncoder = $passwordEncoder;
    }
    public function load(ObjectManager $manager)
    {
         $user = new User();
         $user->setEmail("user@g.g");
         $user->setPassword($this->passwordEncoder->encodePassword($user,"user"));
         $user->setImageName("default.png");
         $manager->persist($user);
        $user2 = new User();
        $user2->setEmail("admin@g.g");
        $user2->setPassword($this->passwordEncoder->encodePassword($user2,"admin"));
        $user2->setRoles(["ROLE_ADMIN"]);
        $user2->setImageName("default.png");
        $manager->persist($user2);
        $array = [$user , $user2];
        for($i = 0; $i< 20 ; $i++){
            $forum = new Forum();
            $forum->setSujet("Forum".$i);
            $rand = rand(0,1);
            $forum->setUser($array[$rand]);
            $manager->persist($forum);
        }

        $date = new \DateTime("now");
        for($i = 0; $i< 20 ; $i++){
            $annonce = new Annonce();
            $annonce->setNom("Annonce ".$i);
            $annonce->setDescription("Vend du fromage ".$i);
            $annonce->setDate($date);
            $rand = rand(0,1);
            $annonce->setUser($array[$rand]);
            $manager->persist($annonce);
        }
        for($i = 0; $i< 20 ; $i++){
            $message = new Message();
            $message->setText(" le message".$i);
            $rand = rand(0,1);
            $message->setUser($array[$rand]);
            $message->setDatePublication($date);
            $message->setForum($forum);
            $manager->persist($message);
        }

        $manager->flush();
    }
}
