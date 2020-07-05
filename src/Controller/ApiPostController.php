<?php

namespace App\Controller;

use App\Entity\Post;
use App\Services\FileUploader;
use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ApiPostController extends AbstractController {
     
    /**
     * @Route("/api/publish", name="api_publish", methods={"POST"})
     */
    public function publish(Request $request, FileUploader $uploader, EntityManagerInterface $manager, MessageBusInterface $bus)
    {   
        $post = new Post();
        $subscribers = $this->getUser()->getFollowers();
        $targets = array();

        foreach ($subscribers as $key) {
            $id = $key->getFollower()->getId();
            $url = "http://realtime/user/{$id}";

            array_push($targets, $url);
        }

        $notif = $this->getUser()->getUsername() . " a posté ! Soyez le premier à commenter son post !";

        $update = new Update(
        	'http://realtime/topics/posts',
        	json_encode(['notif' => $notif]),
            $targets
        );

        $file = $request->files->get("file");

        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

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


        if (in_array($ext, $allowedTypes)) 
        {

            $fileName = $uploader->upload($file, "/posts/");
            $post->setPath('/posts/' . $fileName)
                 ->setUser($this->getUser())
                 ->setDescription($request->request->get("description"))
            ;

            if (in_array($ext, $img)) {
                $post->setType("image/" . $ext);
            } elseif (in_array($ext, $video)) {
                $post->setType("video/" . $ext);
            }else {
                $post->setType("audio/" . $ext);
            }

            $manager->persist($post);
            $manager->flush();

            return $this->json(["status" => "success", "name" => $fileName]);
        }
        return $this->json(["status" => "failed"]);
    }

    /**
     * @Route("/api/change_pic", name="api_pic_change", methods={"POST"})
     */
    public function ppChange(Request $request, FileUploader $fuploader, EntityManagerInterface $manager)
    {
       $File = $request->files->get("file");
       $ext = pathinfo($File->getClientOriginalName(), PATHINFO_EXTENSION);

       $authorized = [
           'png',
           'jpg',
           'jpeg'
       ];

       if (in_array($ext, $authorized)){
            $fileName = $fuploader->upload($File, "/posts/");
            $user = $this->getUser();
            $user->setAvatar("/posts/" . $fileName);

            $manager->persist($user);
            $manager->flush();

            return $this->json(["status" => "success", "file" => $fileName]);
       }

       return $this->json(["status" => "failed"]);

    }
}