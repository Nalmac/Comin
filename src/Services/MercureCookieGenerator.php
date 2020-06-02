<?php 

namespace App\Services;

use App\Entity\User;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha384;


class MercureCookieGenerator
{
	
	public function __construct(string $secret)
	{
		$this->secret = $secret;
	}
	
	public function generate(User $user)
	{
		$token = (new Builder())
				->set('mercure', ['subscribe' => ["http://realtime/user/{$user->getId()}"]])
				->sign(new Sha384(), $this->secret)
				->getToken();

		return "mercureAuthorization={$token}; Path=/.well-known/mercure; HttpOnly;";
	}
}