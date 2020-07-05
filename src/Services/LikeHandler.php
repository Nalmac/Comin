<?php 

namespace App\Services;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;

class LikeHandler
{
	
	private $user;

	private $post;
	
	function __construct(User $user, Post $post)
	{
		$this->user = $user;
		$this->post = $post;
	}

	

	public function like(EntityManagerInterface $manager, MessageBusInterface $bus, PostLikeRepository $likeRepo)
	{
		
		if(!$this->post->isLikedByUser($this->user)){
			try {
				$like = new PostLike($this->user, $this->post);
				$postUserId = $this->post->getUser()->getId();

				$update = new Update(
					"http://realtime/posts/like",
					json_encode(['notif' => "{$this->user->getUsername()} a aimé votre publication", "use" => "like"]),
					["http://realtime/user/{$postUserId}"]
				);

				$this->post->addPostLike($like);

				$manager->persist($this->post);
				$manager->persist($like);
				$manager->flush();

				$generalUpdate = new Update(
					"http://realtime/posts/like",
					json_encode(['notif' => 'Like', "use" => null,'post' => $this->post->getId(), 'likes' => $likeRepo->count(['post' => $this->post])])
				);

				$bus->dispatch($update);
				$bus->dispatch($generalUpdate);

				return ["status" => "success", "action" => "add", "likes" => $likeRepo->count(["post" => $this->post])];
			} catch (Exception $e) {
				return ["status" => "failed", "exception" => $e];
			}
		}else{
	
			try {
				
				$like = $likeRepo->findBy(["user" => $this->user, "post" => $this->post]);
				$this->post->removePostLike($like[0]);
				$manager->remove($like[0]);

				$manager->flush();

				$generalUpdate = new Update(
					"http://realtime/posts/like",
					json_encode(['notif' => 'Like', 'post' => $this->post->getId(), 'likes' => $likeRepo->count(['post' => $this->post])])
				);
				$bus->dispatch($generalUpdate);

				return ["status" => "success", "action" => "remove","likes" => $likeRepo->count(["post" => $this->post])];


			} catch (Exception $e) {
				return ["status" => "failed", "exception" => $e];
			}
		}
			
	}

}
