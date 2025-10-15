<?php

namespace App\Entity;

use App\Repository\CustomersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomersRepository::class)]
class Customers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $customer_name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Email is required.')] 
    #[Assert\Email(
        message: 'Please enter a valid email address.',
        mode: 'strict', // 👈 strict mode rejects incomplete domains
    )]
    private ?string $customer_email = null;

    #[ORM\Column(length: 255)]
    private ?string $customer_password = null;

    #[ORM\Column(length: 255)]
    private ?string $customer_address = null;

    #[ORM\Column]
    private ?int $customer_phonenumber = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerName(): ?string
    {
        return $this->customer_name;
    }

    public function setCustomerName(string $customer_name): static
    {
        $this->customer_name = $customer_name;

        return $this;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customer_email;
    }

    public function setCustomerEmail(string $customer_email): static
    {
        $this->customer_email = $customer_email;

        return $this;
    }

    public function getCustomerPassword(): ?string
    {
        return $this->customer_password;
    }

    public function setCustomerPassword(string $customer_password): static
    {
        $this->customer_password = $customer_password;

        return $this;
    }

    public function getCustomerAddress(): ?string
    {
        return $this->customer_address;
    }

    public function setCustomerAddress(string $customer_address): static
    {
        $this->customer_address = $customer_address;

        return $this;
    }

    public function getCustomerPhonenumber(): ?int
    {
        return $this->customer_phonenumber;
    }

    public function setCustomerPhonenumber(int $customer_phonenumber): static
    {
        $this->customer_phonenumber = $customer_phonenumber;

        return $this;
    }
}
