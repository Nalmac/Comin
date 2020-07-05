<?php

namespace App\DataPersisters;

use App\Entity\Comment;
use App\Services\CommentHandler;
use App\Repository\CommentRepository;
use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentPersister extends AbstractController implements DataPersisterInterface {

    protected $em;
    protected $handler;
    protected $repo;
    protected $bus;

    public function __construct(EntityManagerInterface $em, MessageBusInterface $bus, CommentRepository $repo)
    {
        $this->em = $em;
        $this->repo = $repo;
        $this->bus = $bus;
    }

    public function supports($data) : bool
    {
        return $data instanceof Comment;
    }

    public function persist($data)
    {   
        if ($this->getUser()->getId() === $data->getUser()->getId()) {
            $update = new Update(
                "http://realtime/posts/comment",
                json_encode(['notif' => "{$this->getUser()->getUsername()} a commenté votre publication : {$data->getContent()}"]),
                ["http://realtime/user/{$data->getPost()->getUser()->getId()}"]
            );
    
            $data->getPost()->addComment($data);
    
            $this->em->persist($data->getPost());
            $this->em->persist($data);
    
            $this->em->flush();
    
            $generalUpdate = new Update(
                "http://realtime/posts/comment",
                json_encode(['notif' => 'comment', 'post' => $data->getPost()->getId(), 'comments' => $this->repo->count(["post" => $data->getPost()])])
            );
    
            $this->bus->dispatch($update);
        }

    }

    public function remove($data)
    {
        if ($this->getUser()->getId() === $data->getUser()->getId()) {
            $this->em->remove($data);
            $this->em->flush();
        }
    }
}