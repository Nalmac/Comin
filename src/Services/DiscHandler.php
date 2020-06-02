<?php

namespace App\Services;

use App\Entity\Disc;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * 
 */
class DiscHandler
{	
	function __construct(User $user1, User $user2)
	{
		$this->disc = new Disc();
		$this->disc->setUser1($user1)
				   ->setUser2($user2)
				   ->setNew(0)
				   ->setNew2(0)
		;
	}

	public function create(EntityManagerInterface $manager, MessageBusInterface $bus)
	{

		$manager->persist($this->disc);
		$manager->flush();

		$data = [
			"message" => "success",
			"disc" => $this->disc->getUser2()->getUsername(),
			"new" => $this->disc->getNew()
		];

		return $data;
	}
}