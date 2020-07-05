<?php

namespace App\DataPersisters;

use App\Entity\Message;
use App\Services\ChatHandler;
use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessagePersister extends AbstractController implements DataPersisterInterface {

    protected $bus;
    protected $em;

    public function __construct(EntityManagerInterface $em, MessageBusInterface $bus)
    {
        $this->em = $em;
        $this->bus = $bus;
    }

    public function supports($data): bool
    {
        return $data instanceof Message;
    }

    public function persist($data)
    {
        if ($this->getUser()->getId() === $data->getSender()->getId()) {
            
            try {
                $message = $data;
    
                $target = ($data->getDisc()->getUser1() === $data->getSender()) ? $data->getDisc()->getUser2() : $data->getDisc()->getUser1() ;
    
                $data->getDisc()->addMessage($message);
    
                if ($target === $data->getDisc()->getUser1()) {
                    $new = $data->getDisc()->getNew() + 1;
                    $data->getDisc()->setNew($new);
                }else{
                    $new = $data->getDisc()->getNew2() + 1;
                    $data->getDisc()->setNew2($new);
                }
    
                $update = new Update(
                    "http://realtime/chat/msg",
                    json_encode(["notif" => "message", "message" => ['user' => $message->getSender()->getUsername(), 'content' => $message->getContent(), 'disc' => $message->getDisc()->getId(), "new" => $new]]),
                    ["http://realtime/user/{$target->getId()}"]
                );
    
                
    
                $this->em->persist($data->getDisc());
                $this->em->persist($message);
    
                $this->em->flush();
    
                $this->bus->dispatch($update);
    
    
            } catch (Exception $e) {
            }
        }
    }

    public function remove($data)
    {
        if ($this->getUser()->getId() === $data->getSender()->getId()) {
            $this->em->remove($data);
            $this->em->flush();
        }
    }
}