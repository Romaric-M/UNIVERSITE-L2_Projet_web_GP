<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Forum;
use App\Entity\User;

use App\Form\AnnonceType;
use App\Form\EditUserType;
use App\Form\ForumType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController {

    /**
     * @Route("/admin",name="admin")
     */
    public function index(): Response {
        $admin = $this->getUser();
        return $this->render('admin/index.html.twig', [
            'controller_name' => "ADMIN",
            'admin' => $admin
        ]);
    }

    /**
     * @Route("/admin/annonce",name="admin-annonce")
     */
    public function annonce(): Response
    {
        $tab= $this->getDoctrine()->getRepository(Annonce::class)->findAll();
        return $this->render('admin/donnee.html.twig', [
            'controller_name' => "ADMIN",
            "tab"=>$tab,
            "route" => "annonce"
        ]);
    }

    /**
     * @Route("/admin/forum",name="admin-forum")
     */
    public function forum(): Response {
        $tab= $this->getDoctrine()->getRepository(Forum::class)->findAll();
        return $this->render('admin/donnee.html.twig', [
            'controller_name' => "ADMIN",
            "tab"=>$tab,
            "route" => "forum"
        ]);
    }

    /**
     * @Route("/admin/user",name="admin-user")
     */
    public function user(): Response {
        $tab= $this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('admin/donnee.html.twig', [
            'controller_name' => "ADMIN",
            "tab"=>$tab,
            "route" => "user"
        ]);
    }

    /**
     * @Route("/admin/remove/{route}-{id}",requirements={"route":"[a-z]*","id":"\d*"},name="remove")
     */
    public function remove( $route ,  $id) : Response {
        switch ($route){
            case "forum":
                $object = $this->getDoctrine()->getRepository(Forum::class)->find($id);
                break;

                case "user":
                    $object = $this->getDoctrine()->getRepository(User::class)->find($id);
                    break;

                    case "annonce":
                        $object = $this->getDoctrine()->getRepository(Annonce::class)->find($id);
                        break;

                        default:
                            return $this->redirectToRoute("admin");
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();
        return $this->redirectToRoute("admin-".$route);
    }

    /**
     * @Route("/admin/add/{route}",requirements={"route":"[a-z]*"},name="add")
     */
    public function add($route, Request $request, UserPasswordEncoderInterface $passwordEncoder) :Response
    {
        switch ($route){
            case "forum":
                $object = new Forum();
                $form = $this->createForm(ForumType::class,$object);
                break;
                case "user":
                    $object = new User();
                    $form = $this->createForm(UserType::class,$object);
                    break;

                    case "annonce":
                        $object = new Annonce();
                        $form = $this->createForm(AnnonceType::class,$object);
                        break;

                        default:
                            return $this->redirectToRoute("admin");
        }
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            if($route === "user"){
                $object->setPassword($passwordEncoder->encodePassword($object,$form->get('password')->getData()));
                if($object->getImageName() == null)
                {
                    $object->setImageName("default.png");
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($object);
            $em->flush();
            return $this->redirectToRoute("admin-".$route);
        }
        return $this->render('admin/ajout.html.twig', [
            'controller_name' => "ADMIN",
            "form"=>$form->createView(),
        ]);
    }

    /**
     * @Route("/admin/edit/{route}-{id}",requirements={"route":"[a-z]*","id":"\d*"},name="edit")
     */
    public function edit($route, $id,Request $request) :Response
    {
        switch ($route){
            case "forum":
                $object = $this->getDoctrine()->getRepository(Forum::class)->find($id);
                $form = $this->createForm(ForumType::class,$object);
                break;

                case "user":
                    $object = $this->getDoctrine()->getRepository(User::class)->find($id);
                    $form = $this->createForm(EditUserType::class,$object);
                    break;

                    case "annonce":
                        $object = $this->getDoctrine()->getRepository(Annonce::class)->find($id);
                        $form = $this->createForm(AnnonceType::class,$object);
                        break;

                        default:
                            return $this->redirectToRoute("admin");
        }
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($object);
            $em->flush();
            return $this->redirectToRoute("admin-".$route);
        }
        return $this->render('admin/ajout.html.twig', [
            'controller_name' => "ADMIN",
            "form"=>$form->createView(),
        ]);
    }
}
