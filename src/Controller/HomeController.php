<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Services\MercureCookieGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/home/get_user_id", name="home_user_id")
     */
    public function test(SessionInterface $session, MercureCookieGenerator $cookieGenerator)
    {
        $user = $this->getUser();

        $response = $this->json($user);
        $response->headers->set('set-cookie', $cookieGenerator->generate($this->getUser()));

        return $response;
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

    public function jsonLogin(Request $request, SessionInterface $session)
    {
        $user = $this->getUser();

        return $this->json([
            "registered" => true,
            "username" => $user->getUsername(),
            "roles" => $user->getRoles(),
            "id" => $user->getId(),
            "session" => $session->getId()
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
