<?php

namespace App\Entity;

use App\Enum\SubscriptionType;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
    fields: ['email'],
    message: 'This email is already in use.'
)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Name cannot be empty.")]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Email cannot be empty.")]
    #[Assert\Email(message: "Please enter a valid email address.")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Phone cannot be empty.")]
    #[Assert\Regex(
        pattern: "/^\+?\d+$/",
        message: "Please enter a valid phone number with digits or an optional leading plus sign."
    )]
    #[Assert\Length(
        min: 10,
        max: 15,
        minMessage: "Phone number must be at least {{ limit }} characters long.",
        maxMessage: "Phone number cannot exceed {{ limit }} characters."
    )]
    private ?string $phone = null;

    #[ORM\Column(type: "string", enumType: SubscriptionType::class)]
    #[Assert\NotBlank(message: "Subscription Type cannot be empty.")]
    private ?SubscriptionType $subscriptionType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getSubscriptionType(): ?SubscriptionType
    {
        return $this->subscriptionType;
    }

    public function setSubscriptionType(SubscriptionType $subscriptionType): static
    {
        $this->subscriptionType = $subscriptionType;
        return $this;
    }
}
