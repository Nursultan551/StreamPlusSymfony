<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[Assert\Callback(callback: "validateExpirationDate")]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Credit card number is required.")]
    #[Assert\Length(
        min: 16,
        max: 16,
        exactMessage: "Credit card number must be exactly {{ limit }} digits."
    )]
    #[Assert\Regex(
        pattern: "/^\d+$/",
        message: "Credit card number must contain only digits."
    )]
    private ?string $creditCardNumber = null;

    #[ORM\Column(length: 7)]
    #[Assert\NotBlank(message: "Expiration date is required.")]
    private ?string $expirationDate = null;

    #[ORM\Column(length: 4)]
    #[Assert\NotBlank(message: "CVV is required.")]
    #[Assert\Length(
        min: 3,
        max: 4,
        exactMessage: "CVV must be either 3 or 4 digits."
    )]
    #[Assert\Regex(
        pattern: "/^\d+$/",
        message: "CVV must contain only digits."
    )]
    private ?string $cvv = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true)]
    private ?User $user = null;


    public function validateExpirationDate(ExecutionContextInterface $context): void
    {
        if (!$this->expirationDate) {
            return;
        }

        // Split the input into month and year parts.
        $parts = explode('/', $this->expirationDate);
        if (count($parts) !== 2) {
            $context->buildViolation("Invalid expiration date format. Use MM/YY.")
                ->atPath('expirationDate')
                ->addViolation();
            return;
        }

        $month = (int) $parts[0];

        // Validate month: must be between 1 and 12.
        if ($month < 1 || $month > 12) {
            $context->buildViolation("Month must be between 01 and 12.")
                ->atPath('expirationDate')
                ->addViolation();
            return;
        }

        // Create a DateTime object from "MM/YY" (defaults day to "01")
        $date = \DateTime::createFromFormat('m/y', $this->expirationDate);
        if (!$date) {
            $context->buildViolation("Invalid expiration date format. Use MM/YY.")
                ->atPath('expirationDate')
                ->addViolation();
            return;
        }

        // Adjust to the last day of the month.
        $date->modify('last day of this month');

        if ($date < new \DateTime()) {
            $context->buildViolation("Expiration date must be in the future.")
                ->atPath('expirationDate')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreditCardNumber(): ?string
    {
        return $this->creditCardNumber;
    }

    public function setCreditCardNumber(string $creditCardNumber): static
    {
        $this->creditCardNumber = $creditCardNumber;
        return $this;
    }

    public function getExpirationDate(): ?string
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(string $expirationDate): static
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    public function getCvv(): ?string
    {
        return $this->cvv;
    }

    public function setCvv(string $cvv): static
    {
        $this->cvv = $cvv;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
