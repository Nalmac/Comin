<?php

namespace App\DataPersisters;

use App\Entity\Disc;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DiscPersister extends AbstractController implements DataPersisterInterface {
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em  = $em;
    }

    public function supports($data): bool
    {
        return $data instanceof Disc;
    }

    public function persist($data)
    {
        if ($data->getUser()->getId() === $this->getUser()->getId()) {
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