<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @Route("/home", name="home")
     */
    public function index(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {

        if ($this->getUser()) {
            return $this->redirectToRoute("network");
        }

        $this->redirectToRoute("network");

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);
            $user->setUsername(htmlspecialchars($user->getUsername()));
            $user->setAvatar("/pps/default.png");

            $manager->persist($user);
            $manager->flush();

            return $this->render('home/index.html.twig', [
                'form' => $form->createView(),
                'registered' => true
            ]);
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'registered' => false
        ]);
    }

    /**
     * @Route("/home/test", name="home_test")
     */
    public function test()
    {
        return $this->json([
            "username" => $this->getUser()->getUsername()
        ]);
    }

    /**
    * @Route("/home/logout", name="home_logout")
    */
    public function logout()
    {
    	# code...
    }

    /**
     * @Route("/home/json_login", name="json_login", methods={"POST"})
    */

    public function jsonLogin(Request $request)
    {
        $user = $this->getUser();

        return $this->json([
            "username" => $user->getUsername(),
            "roles" => $user->getRoles()
        ]);
    }

    /**
    * @Route("/home/login", name="home_login")
    */
    public function login()
    {
        # code...
    }
}
