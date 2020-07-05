<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Entity\User;
use App\Form\QueryUserType;
use App\Repository\DiscRepository;
use App\Repository\FollowRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
    * @Route("/user/search", name="user_search")
    */
    public function search(Request $request, UserRepository $usersRepo, DiscRepository $disc)
    {
    	$userQuery = new User();
    	$form = $this->createForm(QueryUserType::class, $userQuery);

    	$form->handleRequest($request);
        $followers = array();
        $users = null;

        $discs1 = $disc->findBy(['user1' => $this->getUser()]);
        $discs2 = $disc->findBy(['user2' => $this->getUser()]);

        $discs = array_merge($discs1, $discs2);
        $talkWith = array();
        foreach ($discs as $disc) {
            $user = ($this->getUser() == $disc->getUser1()) ? $disc->getUser2() : $disc->getUser1() ;
            $talkWith[] = $user;
        }


    	if ($form->isSubmitted() && $form->isValid()) {
    		$users = $usersRepo->findLikeUsername($userQuery->getUsername());
    	}

    	return $this->render('user/search.html.twig', [
            'form' => $form->createView(),
            'users' => $users,
            'discs' => $discs,
            'talkWith' => $talkWith
        ]);
    }

    /**
    * @Route("/user/myaccount", name="user_myaccount")
    */

    public function myAccount(DiscRepository $disc)
    {
        $discs1 = $disc->findBy(['user1' => $this->getUser()]);
        $discs2 = $disc->findBy(['user2' => $this->getUser()]);

        $discs = array_merge($discs1, $discs2);
        $talkWith = array();
        foreach ($discs as $disc) {
            $user = ($this->getUser() == $disc->getUser1()) ? $disc->getUser2() : $disc->getUser1() ;
            $talkWith[] = $user;
        }
        return $this->render('user/myAccount.html.twig', [
            'discs' => $discs,
            'talkWith' => $talkWith
        ]);
    }

    /**
    * @Route("/user/{id}/subscribe", name="user_subscribe")
    */
    public function subscribe(User $followed, MessageBusInterface $bus, EntityManagerInterface $manager)
    {
        $update = new Update(
            "http://realtime/users/subs",
            json_encode(['notif' => "{$this->getUser()->getUsername()} s'est abonné à votre profil !", "use" => "follow"]),
            ["http://realtime/user/{$followed->getId()}"]

        );

        $follow = new Follow($this->getUser(), $followed);

        $followed->addFollower($follow);

        $manager->persist($follow);
        $manager->persist($followed);
        $manager->flush();

        $bus->dispatch($update);

        return $this->redirectToRoute('user', ['id' => $followed->getId()]);

    }

    /**
    * @Route("/user/{id}/unsub", name="user_unsub")
    */
    public function unsub(User $unFollowed, MessageBusInterface $bus, EntityManagerInterface $manager, FollowRepository $follows)
    {

        $follow = $follows->findBy(['followed' => $unFollowed, 'follower' => $this->getUser()]);

        $unFollowed->removeFollower($follow[0]);
        $this->getUser()->removeFollow($follow[0]);
        $manager->remove($follow[0]);
        $manager->persist($unFollowed);

        $manager->flush();

        return $this->redirectToRoute('user', ['id' => $unFollowed->getId()]);
    }

    /**
    * @Route("/user/check", name="user_check")
    */
    public function check(UserPasswordEncoderInterface $encoder)
    {
        if (isset($_POST['password'])) {
            $hash = $encoder->encodePassword($this->getUser(), $_POST['password']);
            if ($this->getUser()->getPassword() == $hash || $this->getUser()->getPassword() == $_POST['password']) {
                return $this->json(['status' => 'success']);
            }
        }

        return $this->json(['status' => 'failure', 'pass' => $this->getUser()->getPassword(), 'given' => $hash]);
    }

    /**
     * @Route("/user/{id}", name="user")
     */
    public function index(UserRepository $users, DiscRepository $disc, User $user)
    {   
        $posts = $user->getPosts();
        $followers = $user->getFollowers();
        $follows = $user->getFollows();

        $discs1 = $disc->findBy(['user1' => $this->getUser()]);
        $discs2 = $disc->findBy(['user2' => $this->getUser()]);
        $discs = array_merge($discs1, $discs2);
        $talkWith = array();
        foreach ($discs as $disc) {
            $userDisc = ($this->getUser() == $disc->getUser1()) ? $disc->getUser2() : $disc->getUser1() ;
            $talkWith[] = $userDisc;
        }
        
        return $this->render('user/index.html.twig', [
            'viewedUser' => $user,
            'posts' => $posts,
            'followers' => $followers,
            'follows' => $follows,
            'discs' => $discs,
            'talkWith' => $talkWith
        ]);
    }

    
}
