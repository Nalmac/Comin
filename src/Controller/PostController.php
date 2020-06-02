<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\DiscRepository;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use App\Services\CommentHandler;
use App\Services\FileUploader;
use App\Services\LikeHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/post", name="post")
     */
    public function index(MessageBusInterface $bus, EntityManagerInterface $manager, Request $request, FileUploader $uploader, DiscRepository $disc)
    {
        $subscribers = $this->getUser()->getFollowers();
        $targets = array();

        foreach ($subscribers as $key) {
            $id = $key->getFollower()->getId();
            $url = "http://realtime/user/{$id}";

            array_push($targets, $url);
        }

        $update = new Update(
        	'http://realtime/topics/posts',
        	json_encode(['notif' => 'post']),
            $targets
        );

        $discs1 = $disc->findBy(['user1' => $this->getUser()]);
        $discs2 = $disc->findBy(['user2' => $this->getUser()]);

        $discs = array_merge($discs1, $discs2);
        $talkwith = array();
        foreach ($discs as $disc) {
            $user = ($this->getUser() == $disc->getUser1()) ? $disc->getUser2() : $disc->getUser1() ;
            $talkWith[] = $user;
        }

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted()) 
        {

            $post->setDescription(htmlspecialchars($post->getDescription()));

        	$file = $form['path']->getData();
            if ($file) {
            	$allowedTypes = array(
            		'jpeg',
            		'jpg',
            		'png',
            		'mp3',
            		'wav',
            		'wma',
            		'mp4',
            		'avi',
            	);

            	$img = array(
            		'jpeg',
            		'jpg',
            		'png'
            	);

            	$audio = array(
            		'mp3',
            		'wav',
            		'wma'
            	);

            	$video = array(
            		'mp4',
            		'avi'
            	);

            	$fileInfo = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

            	if (in_array($fileInfo, $allowedTypes)) 
            	{

            		$fileName = $uploader->upload($file, "/posts/");
            		$post->setPath('/posts/' . $fileName)
            			 ->setUser($this->getUser())
            		;

            		if (in_array($fileInfo, $img)) {
            			$post->setType("image/" . $fileInfo);
            		} elseif (in_array($fileInfo, $video)) {
            			$post->setType("video/" . $fileInfo);
            		}else {
            			$post->setType("audio/" . $fileInfo);
            		}
            		

    	        	$manager->persist($post);
    	        	$manager->flush();

    	        	$bus->dispatch($update);
            	}

    			 return $this->redirectToRoute("network");
            }
            return $this->json(['error']);      	
        }


        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'discs' => $discs,
            'talkWith' => $talkwith
        ]);
    }

    /**
    * @Route("/post/{id}/like/{from}", name="post_like")
    */

    public function like(Post $post, String $from, MessageBusInterface $bus, EntityManagerInterface $manager, PostLikeRepository $likeRepo) : Response
    {
        $user = $this->getUser();
        $handler = new LikeHandler($user, $post);

        $like = $handler->like($manager, $bus, $likeRepo);

        if ($like["status"] == "success") {
            
            return $this->json($like);

        }else{
            return $this->json($like);
        }
    }

    /**
    * @Route("/post/{id}/comment/{from}", name="post_comment")
    */

    public function comment(Post $post, String $from, MessageBusInterface $bus, EntityManagerInterface $manager, CommentRepository $commentRepo) : JsonResponse
    {
        $user = $this->getUser();
        if (isset($_POST['content']) && $_POST['content'] != "") {
            $content = htmlspecialchars($_POST['content']);
            $handler = new CommentHandler($user, $post, $content);

            $comment = $handler->comment($manager, $bus, $commentRepo);

            return new JsonResponse($comment);
        }

        else{

            return new JsonResponse(["message" => "Bad datas"]);
        }
        
    }
}
