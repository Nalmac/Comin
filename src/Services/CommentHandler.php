<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * 
 */
class CommentHandler
{
	private $user;
	private $post;
	private $content;

	function __construct(User $user, Post $post, String $content)
	{
		$this->user = $user;
		$this->post = $post;
		$this->content = $content;
	}

	public function comment(EntityManagerInterface $manager, MessageBusInterface $bus, CommentRepository $commentRepo)
	{
		$comment = new Comment($this->user, $this->post, $this->content);

		$update = new Update(
			"http://realtime/posts/comment",
			json_encode(['notif' => "{$this->user->getUsername()} a commenté votre publication : {$this->content}"]),
			["http://realtime/user/{$this->post->getUser()->getId()}"]
		);

		$this->post->addComment($comment);

		$manager->persist($this->post);
		$manager->persist($comment);

		$manager->flush();

		$generalUpdate = new Update(
			"http://realtime/posts/comment",
			json_encode(['notif' => 'comment', 'post' => $this->post->getId(), 'comments' => $commentRepo->count(["post" => $this->post])])
		);

		$bus->dispatch($update);

		$commentObjects = $commentRepo->findBy(["post" => $this->post], ["id" => "desc"]);
		$commentsNumber = $commentRepo->count(["post" => $this->post]);

		$comments = [
			"user" => [
					"username" => $comment->getUser()->getUsername(),
					"avatar" => $comment->getUser()->getAvatar()
				],
			"content" => $comment->getContent()
		];

		$data = ["comments" => $comments, "number" => $commentsNumber, "message" => "success"];

		return $data;

	}
}