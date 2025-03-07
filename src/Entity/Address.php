<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: "Address Line 1 is required.")]
    #[Assert\Length(
        min: 5,
        max: 150,
        minMessage: "Address Line 1 must be at least {{ limit }} characters long.",
        maxMessage: "Address Line 1 cannot exceed {{ limit }} characters."
    )]
    private ?string $addressLine1 = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\NotBlank(message: "Address Line 2 is required.")]
    #[Assert\Length(
        max: 150,
        maxMessage: "Address Line 2 cannot exceed {{ limit }} characters."
    )]
    private ?string $addressLine2 = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "City is required.")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "City must be at least {{ limit }} characters long.",
        maxMessage: "City cannot exceed {{ limit }} characters."
    )]
    private ?string $city = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Postal Code is required.")]
    #[Assert\Length(
        min: 4,
        max: 10,
        minMessage: "Postal Code must be at least {{ limit }} characters long.",
        maxMessage: "Postal Code cannot exceed {{ limit }} characters."
    )]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "State is required.")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "State must be at least {{ limit }} characters long.",
        maxMessage: "State cannot exceed {{ limit }} characters."
    )]
    private ?string $state = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Country is required.")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Country must be at least {{ limit }} characters long.",
        maxMessage: "Country cannot exceed {{ limit }} characters."
    )]
    private ?string $country = null;

    // The foreign key is stored in this table.
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(string $addressLine1): static
    {
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function setAddressLine2(?string $addressLine2): static
    {
        $this->addressLine2 = $addressLine2;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
