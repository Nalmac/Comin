<?php

namespace App\DataPersisters;

use App\Entity\Follow;
use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FollowPersister extends AbstractController implements DataPersisterInterface {

    protected $em;
    protected $bus;

    public function __construct(EntityManagerInterface $em, MessageBusInterface $bus)
    {
        $this->em = $em;
        $this->bus = $bus;
    }

    public function supports($data): bool
    {
        return $data instanceof Follow;
    }

    public function persist($data)
    {
        if ($this->getUser()->getId() === $data->getFollower()->getId()) {
            $targetId = $data->getFollowed()->getId();
            $target = "https://realtime/user/{$targetId}";

            $update = new Update(
                "https://realtime/users/subs",
                json_encode(["notif" => "{$this->getUser()->getUsername()} s'est abonné à votre profil !"]),
                $targetId
            );

            $this->em->persist($data);
            $this->em->flush();

            $this->bus->dispatch($update);
        }
    }

    public function remove($data)
    {
        if ($this->getUser()->getId() === $data->getFollower()->getId()) {
            $this->em->remove($data);
            $this->em->flush();
        }
    }
}