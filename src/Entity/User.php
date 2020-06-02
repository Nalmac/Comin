<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
    fields = {"email"},
    message = "L'email entré est déjà utilisé"
 )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=8, minMessage="Votre mot de passe est trop court, il doit faire au moins 8 caractères")
     * @Assert\EqualTo(propertyPath="confirmPassword", message="Les deux mots de passe doivent être identiques")
     */
    private $password;

    public $confirmPassword;

    private $roles = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $avatar;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="user", orphanRemoval=true)
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Disc", mappedBy="user1")
     */
    private $discs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Disc", mappedBy="user2", orphanRemoval=true)
     */
    private $discs2;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Follow", mappedBy="follower", orphanRemoval=true)
     */
    private $follows;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Follow", mappedBy="followed", orphanRemoval=true)
     */
    private $followers;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;




    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->discs = new ArrayCollection();
        $this->discs2 = new ArrayCollection();
        $this->follows = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Disc[]
     */
    public function getDiscs(): Collection
    {
        return $this->discs;
    }

    public function addDisc(Disc $disc): self
    {
        if (!$this->discs->contains($disc)) {
            $this->discs[] = $disc;
            $disc->setUser1($this);
        }

        return $this;
    }

    public function removeDisc(Disc $disc): self
    {
        if ($this->discs->contains($disc)) {
            $this->discs->removeElement($disc);
            // set the owning side to null (unless already changed)
            if ($disc->getUser1() === $this) {
                $disc->setUser1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Disc[]
     */
    public function getDiscs2(): Collection
    {
        return $this->discs2;
    }

    public function addDiscs2(Disc $discs2): self
    {
        if (!$this->discs2->contains($discs2)) {
            $this->discs2[] = $discs2;
            $discs2->setUser2($this);
        }

        return $this;
    }

    public function removeDiscs2(Disc $discs2): self
    {
        if ($this->discs2->contains($discs2)) {
            $this->discs2->removeElement($discs2);
            // set the owning side to null (unless already changed)
            if ($discs2->getUser2() === $this) {
                $discs2->setUser2(null);
            }
        }

        return $this;
    }

    public function eraseCredentials()
    {
        # code...
    }

    public function getSalt()
    {
        # code...
    }

    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $role): self
    {
        $this->roles = $role;
        return $this;
    }

    /**
     * @return Collection|Follow[]
     */
    public function getFollows(): Collection
    {
        return $this->follows;
    }

    public function addFollow(Follow $follow): self
    {
        if (!$this->follows->contains($follow)) {
            $this->follows[] = $follow;
            $follow->setFollower($this);
        }

        return $this;
    }

    public function removeFollow(Follow $follow): self
    {
        if ($this->follows->contains($follow)) {
            $this->follows->removeElement($follow);
            // set the owning side to null (unless already changed)
            if ($follow->getFollower() === $this) {
                $follow->setFollower(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Follow[]
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(Follow $follower): self
    {
        if (!$this->followers->contains($follower)) {
            $this->followers[] = $follower;
            $follower->setFollowed($this);
        }

        return $this;
    }

    public function removeFollower(Follow $follower): self
    {
        if ($this->followers->contains($follower)) {
            $this->followers->removeElement($follower);
            // set the owning side to null (unless already changed)
            if ($follower->getFollowed() === $this) {
                $follower->setFollowed(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }



    
}
