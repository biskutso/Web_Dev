<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Products $productId = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Services $serviceId = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Categories $categoryId = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $order_created = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $createdBy = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProductId(): ?Products
    {
        return $this->productId;
    }

    public function setProductId(?Products $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function getServiceId(): ?Services
    {
        return $this->serviceId;
    }

    public function setServiceId(?Services $serviceId): static
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    public function getCategoryId(): ?Categories
    {
        return $this->categoryId;
    }

    public function setCategoryId(?Categories $categoryId): static
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getOrderCreated(): ?\DateTimeImmutable
    {
        return $this->order_created;
    }

    public function setOrderCreated(\DateTimeImmutable $order_created): static
    {
        $this->order_created = $order_created;

        return $this;
    }

    public function __construct()
    {
        $this->order_created = new \DateTimeImmutable();
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
