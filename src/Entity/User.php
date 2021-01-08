<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`users`")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank(message="Please insert an email.")
     * @Assert\Email(message="Please insert a valid email.")
     */
    private $email;


    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password_digest;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $created_at;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $updated_at;



    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $remember_digest;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $admin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $activation_digest;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $activated;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $activated_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reset_digest;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reset_sent_at;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please insert a name.")
     */
    private $name;


    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password_digest;
    }

    public function setPassword(string $password): self
    {
        $this->password_digest = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(string $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getPasswordDigest(): ?string
    {
        return $this->password_digest;
    }

    public function setPasswordDigest(string $password_digest): self
    {
        $this->password_digest = $password_digest;

        return $this;
    }

    public function getRememberDigest(): ?string
    {
        return $this->remember_digest;
    }

    public function setRememberDigest(?string $remember_digest): self
    {
        $this->remember_digest = $remember_digest;

        return $this;
    }

    public function getAdmin(): ?int
    {
        return $this->admin;
    }

    public function setAdmin(?int $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getActivationDigest(): ?string
    {
        return $this->activation_digest;
    }

    public function setActivationDigest(?string $activation_digest): self
    {
        $this->activation_digest = $activation_digest;

        return $this;
    }

    public function getActivated(): ?string
    {
        return $this->activated;
    }

    public function setActivated(?string $activated): self
    {
        $this->activated = $activated;

        return $this;
    }

    public function getActivatedAt(): ?string
    {
        return $this->activated_at;
    }

    public function setActivatedAt(?string $activated_at): self
    {
        $this->activated_at = $activated_at;

        return $this;
    }

    public function getResetDigest(): ?string
    {
        return $this->reset_digest;
    }

    public function setResetDigest(?string $reset_digest): self
    {
        $this->reset_digest = $reset_digest;

        return $this;
    }

    public function getResetSentAt(): ?string
    {
        return $this->reset_sent_at;
    }

    public function setResetSentAt(?string $reset_sent_at): self
    {
        $this->reset_sent_at = $reset_sent_at;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
