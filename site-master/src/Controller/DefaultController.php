<?php

namespace App\Controller;


use App\Entity\Annonce;
use App\Entity\Forum;
use App\Entity\Message;
use App\Entity\User;
use App\Form\AnnonceType;
use App\Form\CreateAnnonceType;
use App\Form\ForumType;
use App\Form\CreateForumType;
use App\Form\InscriptionType;
use App\Form\MessageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        $forum = $this->getDoctrine()->getRepository(Forum::class)->block(5);
        /* Pas bien d'oublier les annonces :) */
        $annonce = $this->getDoctrine()->getRepository(Annonce::class)->block(5);

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            "forum"=>$forum,
            'annonce' => $annonce

        ]);
    }
    /**
     * @Route("/inscription",name="signin")
     */
    public function signin(Request $request, UserPasswordEncoderInterface $passwordEncoder){
        $user = new User();
        $form= $this->createForm(InscriptionType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user->setImageName('default.png');
            $user->setPassword($passwordEncoder->encodePassword($user,$user->getPassword()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("homepage");
        }
        return $this->render('default/signin.html.twig',['form'=>$form->createView()]);
    }
    /**
     * @Route("/profil",name="profil")
     */
    public function profil(){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //il faut etre connecté pour voir son profil
        $user = $this->getUser();

        $annonces = $this->getDoctrine()->getRepository(Annonce::class)->findBy(['user' => $user]);
        //find annonces créées par l'utilisateur

        return $this->render('default/profil.html.twig', [
            'controller_name' => 'DefaultController',
            'user' => $user,
            'annonces' => $annonces,
        ]);
    }
    /**
     * @Route("/forum",name="forum")
     */
    public function forum(Request $request){
        $forum = $this->getDoctrine()->getRepository(Forum::class)->findAll();
        $form= $this->createForm(CreateForumType::class,null);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
            $date = new \DateTime();
            $new_forum = new Forum();
            $new_forum->setSujet($form->get('sujet')->getData());
            $new_forum->setUser($user);
            $message = new Message();
            $message->setText($form->get('message')->get("text")->getData());
            $message->setUser($user);
            $message->setForum($new_forum);
            $message->setDatePublication($date);
            $em = $this->getDoctrine()->getManager();
            $em->persist($new_forum);
            $em->persist($message);
           $em->flush();
           return $this->redirectToRoute("forum");
        }
        return $this->render("default/forum.html.twig",
        ["forum"=>$forum,
            "form"=>$form->createView()
        ]);
    }
    /**
     * @Route("/forum/{id}",requirements={"id": "\d*"},name="message")
     */
    public function message($id, Request $request){

        $forum = $this->getDoctrine()->getRepository(Forum::class)->findOneBy(['id' => $id]);
        $post = new Message();
        $form = $this->createForm(MessageType::class,$post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $date = new \DateTime();
            $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
            $post->setDatePublication($date);
            $post->setUser($user);
            $post->setForum($forum);
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
        }
        if ($forum !== null){
            //si le forum existe
            $messages = $this->getDoctrine()->getRepository(Message::class)->findMessage($id);
        }else{
            $messages = null;
        }


        return $this->render('default/message.html.twig', [
            'controller_name' => 'DefaultController',
            'forum' => $forum,
            'messages' => $messages,
            'form'=>$form->createView(),
        ]);
    }
    /**
     * @Route("/annonce",name="annonce")
     */
    public function annonce(Request $request){
        $annonce= $this->getDoctrine()->getRepository(Annonce::class)->findAll();
        $new_annonce = new Annonce();
        $form= $this->createForm(CreateAnnonceType::class,$new_annonce);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $date = new \DateTime();
            $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
            $new_annonce->setUser($user);
            $new_annonce->setDate($date);
            $em= $this->getDoctrine()->getManager();
            $em->persist($new_annonce);
            $em->flush();
            return $this->redirectToRoute("annonce");
        }
        return $this->render('default/annonce.html.twig',[
            "annonce"=>$annonce,
            "form"=>$form->createView()
        ]);
    }
    /**
     * @Route("/annonce-add-{id}",requirements={"id":"\d*"},name="annonce-user")
     */
    public function annonceadd($id){
        $annonce=$this->getDoctrine()->getRepository(Annonce::class)->find($id);
        $user = $this->getUser();
        if($user === null){
            $this->redirectToRoute("app_login");
        }
        $users = $this->getDoctrine()->getRepository(User::class)->find($user);
        $annonce->setReply($users);
        $em = $this->getDoctrine()->getManager();
        $em->persist($annonce);
        $em->flush();
        return $this->redirectToRoute('annonce');
    }
}
