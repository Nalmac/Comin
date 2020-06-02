<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DiscRepository")
 */
class Disc
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="discs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="discs2")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user2;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="disc", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\Column(type="float")
     */
    private $new;

    /**
     * @ORM\Column(type="float")
     */
    private $new2;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser1(): ?User
    {
        return $this->user1;
    }

    public function setUser1(?User $user1): self
    {
        $this->user1 = $user1;

        return $this;
    }

    public function getUser2(): ?User
    {
        return $this->user2;
    }

    public function setUser2(?User $user2): self
    {
        $this->user2 = $user2;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setDisc($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getDisc() === $this) {
                $message->setDisc(null);
            }
        }

        return $this;
    }

    public function getNew(): ?float
    {
        return $this->new;
    }

    public function setNew(float $new): self
    {
        $this->new = $new;

        return $this;
    }

    public function getNew2(): ?float
    {
        return $this->new2;
    }

    public function setNew2(float $new2): self
    {
        $this->new2 = $new2;

        return $this;
    }
}
