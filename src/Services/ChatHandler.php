<?php

namespace App\Services;

use App\Entity\Disc;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * 
 */
class ChatHandler
{
	
	function __construct(Disc $disc, User $sender, String $content)
	{
		$this->disc = $disc;
        $this->sender = $sender;
        $this->content = $content;
	}

	public function sendMessage(EntityManagerInterface $manager, MessageBusInterface $bus)
	{
		
		try {
			$message = new Message($this->disc, $this->sender, $this->content);

			$target = ($this->disc->getUser1() == $this->sender) ? $this->disc->getUser2() : $this->disc->getUser1() ;

			$this->disc->addMessage($message);

			if ($target == $this->disc->getUser1()) {
				$new = $this->disc->getNew() + 1;
				$this->disc->setNew($new);
			}else{
				$new = $this->disc->getNew2() + 1;
				$this->disc->setNew2($new);
			}

			$update = new Update(
				"http://realtime/chat/msg",
				json_encode(["notif" => "message", "message" => ['user' => $message->getSender()->getUsername(), 'content' => $message->getContent(), 'disc' => $message->getDisc()->getId(), "new" => $new]]),
				["http://realtime/user/{$target->getId()}"]
			);

			

			$manager->persist($this->disc);
			$manager->persist($message);

			$manager->flush();

			$bus->dispatch($update);

			$data = [
				"disc" => $this->disc->getId(),
				"new" => $new,
				"message" => $message->getContent(),
				"status" => "success",
				"exception" => "none"
			];


		} catch (Exception $e) {
			$data = [
				"status" => "error",
				"exception" => $e
			];
		}
		
		return $data;

	}

}

