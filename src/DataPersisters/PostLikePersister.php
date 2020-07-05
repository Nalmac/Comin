<?php

namespace App\DataPersisters;

use App\Entity\PostLike;
use App\Services\LikeHandler;
use Symfony\Component\Mercure\Update;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostLikePersister extends AbstractController implements DataPersisterInterface {
    protected $em;
    protected $bus;
    protected $handler;
    protected $repo;

    public function __construct(EntityManagerInterface $em, MessageBusInterface $bus, PostLikeRepository $repo)
    {
        $this->em = $em;
        $this->bus = $bus;
        $this->repo = $repo;
    }

    public function supports($data): bool
    {
        return $data instanceof PostLike;
    }

    public function persist($data)
    {
        if ($this->getUser()->getId() === $data->getUser()->getId()) {
            if (!$data->getPost()->isLikedByUser($this->getUser())) {
                try {
                    $like = $data;
                    $postUserId = $this->getUser()->getId();

                    $update = new Update(
                        "http://realtime/posts/like",
                        json_encode(['notif' => "{$this->getUser()->getUsername()} a aimé votre publication"]),
                        ["http://realtime/user/{$postUserId}"]
                    );

                    $data->getPost()->addPostLike($like);

                    $this->em->persist($data->getPost());
                    $this->em->persist($like);
                    $this->em->flush();

                    $generalUpdate = new Update(
                        "http://realtime/posts/like",
                        json_encode(['notif' => 'Like', 'post' => $data->getPost()->getId(), 'likes' => $this->repo->count(['post' => $data->getPost()])])
                    );

                    $this->bus->dispatch($update);
                    $this->bus->dispatch($generalUpdate);

                } catch (Exception $e) {
                }
            }
        }
    }

    public function remove($data)
    {
        if ($this->getUser()->getId() === $data->getUser()->getId()) {
            if ($data->getPost()->isLikedByUser($this->getUser())) {
                try {
                        
                    $like = $this->repo->findBy(["user" => $this->getUser(), "post" => $data->getPost()]);
                    $data->getPost()->removePostLike($like[0]);
                    $this->em->remove($like[0]);

                    $this->em->flush();

                    $generalUpdate = new Update(
                        "http://realtime/posts/like",
                        json_encode(['notif' => 'Like', 'post' => $data->getPost()->getId(), 'likes' => $this->repo->count(['post' => $data->getPost()])])
                    );
                    $this->bus->dispatch($generalUpdate);



                } catch (Exception $e) {
                }
            }
        }

    }
}