<?php

namespace App\DataPersisters;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentPersister extends AbstractController implements DataPersisterInterface {

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function supports($data) : bool
    {
        return $data instanceof Comment;
    }

    public function persist($data)
    {   
        if ($this->getUser()->getId() === $data->getUser()->getId()) {
            $this->em->persist($data);
            $this->em->flush();
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