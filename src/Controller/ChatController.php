<?php

namespace App\Controller;

use App\Entity\Disc;
use App\Entity\User;
use App\Services\ChatHandler;
use App\Services\DiscHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    /**
     * @Route("/chat/{id}", name="chat")
     */
    public function sendMessage(Disc $disc, EntityManagerInterface $manager, MessageBusInterface $bus)
    {
        if ($_POST['content']) {
        	$content = htmlspecialchars($_POST['content']);
        	$handler = new ChatHandler($disc, $this->getUser(), $content);

        	$message = $handler->sendMessage($manager, $bus);
            $message["user_data"] = ["from" => $this->getUser()->getUsername()];

        	return $this->json($message);
        }
        
    }

    /**
    * @Route("/chat/disc/{id}", name="chat_new")
    */
    public function newDisc(User $user, EntityManagerInterface $manager, MessageBusInterface $bus)
    {
    	$handler = new DiscHandler($this->getUser(), $user);

    	$disc = $handler->create($manager, $bus);

    	return $this->json($disc);
    }

    /**
    * @Route("/chat/read/{id}", name="chat_read")
    */
    public function read(Disc $disc, EntityManagerInterface $manager)
    {
        if ($this->getUser() == $disc->getUser1()) {
            $disc->setNew(0);
        }else{
            $disc->setNew2(0);
        }

        $manager->persist($disc);
        $manager->flush();

        return $this->json(['status' => "success"]);
    }
}
