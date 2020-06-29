<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MobileUserController extends AbstractController
{
    /**
     * @Route("/mobile/login/{email}/{password}", name="mobile_login")
     */
    public function login(UserRepository $manager,Request $request, $email, $password, UserPasswordEncoderInterface $encoder)
    {
        $user = $manager->findOneBy(["email" => $email]);
        $password = $encoder->encodePassword($user, $password);
        if ($user->getPassword() == $password) {
        return $this->json([
            "user" => [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "pass_hashed" => $user->getPassword(),
                "email" => $user->getEmail(),
                "avatar_path" => $user->getAvatar(),
                "description" => $user->getDescription()
            ],
            "status" => True
        ]); 
        }
        else{
            return $this->json([
                "user" => False,
                "status" => False
            ]);
        }
    }

    /**
    * @Route("/mobile/session/{id}/{session_id}")
    */
    public function authSession(User $user, $session_id)
    {
        $server_session = $user->getUsername() . $user->getPassword();
        if ($server_session == $session_id) {
            return $this->json(["status" => True]);
        }

    }

    /**
     * @Route("/mobile/get/posts/{id}", name="mobile_posts")
     */
    public function getPosts(User $user, PostRepository $post_repo)
    {
        $posts = $post_repo->findBy(["user" => $user]);
    }
}
