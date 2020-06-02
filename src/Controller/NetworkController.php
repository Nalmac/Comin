<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordModifyType;
use App\Form\UserModifyType;
use App\Repository\DiscRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Services\FileUploader;
use App\Services\MercureCookieGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class NetworkController extends AbstractController
{
    /**
     * @Route("/network", name="network")
     */
    public function index(PostRepository $post, DiscRepository $disc, MercureCookieGenerator $cookieGenerator)
    {
    	$follows = $this->getUser()->getFollows();


        $posts = $post->findBy([], ["id" => "desc"]); 
        $discs1 = $disc->findBy(['user1' => $this->getUser()]);
        $discs2 = $disc->findBy(['user2' => $this->getUser()]);

        $discs = array_merge($discs1, $discs2);
        $postsGood = array();
        $talkWith = array();

        foreach ($discs as $disc) {
            $user = ($this->getUser() == $disc->getUser1()) ? $disc->getUser2() : $disc->getUser1() ;
            $talkWith[] = $user;
        }

        $followers = $this->getUser()->getFollowers();

        foreach ($posts as $key) {
           foreach ($follows as $follow) {
               if ($key->getUser() == $follow->getFollowed()) {
                   array_push($postsGood, $key);
               }
           }
        }



        $response = $this->render('network/index.html.twig', [
        	'posts' => $postsGood,
            'discs' => $discs,
            'followers' => $followers,
            'talkWith' => $talkWith
        ]);
        $response->headers->set('set-cookie', $cookieGenerator->generate($this->getUser()));
        return $response;

    }

    /**
    * @Route("/network/settings", name="network_settings")
    */

    public function settings(EntityManagerInterface $manager, Request $request, UserPasswordEncoderInterface $encoder, UserRepository $userRepo, FileUploader $uploader, DiscRepository $disc)
    {
    	$user = $this->getUser();
    	$pass = $user->getPassword();

    	$form = $this->createForm(UserModifyType::class, $user);
    	$formPass = $this->createForm(PasswordModifyType::class, $user);
    	
    	$form->handleRequest($request);

    	if ($form->isSubmitted() && $form->isValid()) {
    		$profilePic = $form['avatar']->getData();

    		if ($profilePic) {
                if ($this->getUser()->getAvatar() != "/pps/default.png") {
                    $path ="/home/simon/Dev/Comin/public" . $this->getUser()->getAvatar();
                    exec("rm " . $path);
                }
                
                $ext = pathinfo($profilePic->getClientOriginalName(),  PATHINFO_EXTENSION);
                $auth = [
                    'jpg',
                    'jpeg',
                    'png'
                ];

                if (in_array($ext, $auth)) {
                    $pictureFileName = $uploader->upload($profilePic);
                    $user->setAvatar('/pps/' . $pictureFileName);
                }
	
    		}

            $user->setUsername(htmlspecialchars($user->getUsername()));
            $user->setDescription(htmlspecialchars($user->getDescription()));
    		
    		$manager->persist($user);
    		$manager->flush();
    	}

    	$formPass->handleRequest($request);

    	if ($formPass->isSubmitted() && $formPass->isValid()) {
    		
    		$hash = $encoder->encodePassword($user, $user->getPassword());

    		$user->setPassword($hash);

    		$manager->persist($user);
    		$manager->flush();
    	}

        $discs1 = $disc->findBy(['user1' => $this->getUser()]);
        $discs2 = $disc->findBy(['user2' => $this->getUser()]);

        $discs = array_merge($discs1, $discs2);
        $talkWith = array();
        foreach ($discs as $disc) {
            $user = ($this->getUser() == $disc->getUser1()) ? $disc->getUser2() : $disc->getUser1() ;
            $talkWith[] = $user;
        }

    	return $this->render('/network/settings.html.twig', [
    		'form' => $form->createView(),
    		'pass' => $formPass->createView(),
            'discs' => $discs,
            'talkWith' => $talkWith
    	]);
    }

    /**
    * @Route("/network/delete", name="network_delete")
    */

    public function delete(EntityManagerInterface $manager, PostRepository $postRepo)
    {
        $user = $this->getUser();
        $posts = $postRepo->findBy(['user' => $user]);

        foreach ($posts as $post) {
            exec("rm /home/simon/Dev/Comin/public" . $post->getPath());
        }

        exec("rm /home/simon/Dev/Comin/public" . $user->getAvatar());

        $manager->remove($user);
        $manager->flush();
    }

}