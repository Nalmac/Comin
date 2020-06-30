<?php

namespace App\Entity;

use App\Entity\Disc;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 * @ApiResource(
 *      normalizationContext={"groups"={"read"}}
 * )
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Disc", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $disc;

    /**
     * @Groups("read")
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @Groups("read")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    function __construct(Disc $disc, User $sender, String $content)
    {
        $this->disc = $disc;
        $this->sender = $sender;
        $this->content = $content;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDisc(): ?Disc
    {
        return $this->disc;
    }

    public function setDisc(?Disc $disc): self
    {
        $this->disc = $disc;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }
}
