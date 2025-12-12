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

    // USER who owns the order — safe delete
    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $user = null;

    // PRODUCT — safe delete
    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Products $productId = null;

    // SERVICE — safe delete
    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Services $serviceId = null;

    // CATEGORY — safe delete
    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Categories $categoryId = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $order_created = null;

    // USER who created the order — safe delete
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $createdBy = null;

    // SNAPSHOTS (persisted even if FK is deleted)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $productNameSnapshot = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serviceNameSnapshot = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $categoryNameSnapshot = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userNameSnapshot = null;

    public function __construct()
    {
        $this->order_created = new \DateTimeImmutable();
    }

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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    // SNAPSHOT GETTERS/SETTERS

    public function getProductNameSnapshot(): ?string
    {
        return $this->productNameSnapshot;
    }

    public function setProductNameSnapshot(?string $productNameSnapshot): static
    {
        $this->productNameSnapshot = $productNameSnapshot;
        return $this;
    }

    public function getServiceNameSnapshot(): ?string
    {
        return $this->serviceNameSnapshot;
    }

    public function setServiceNameSnapshot(?string $serviceNameSnapshot): static
    {
        $this->serviceNameSnapshot = $serviceNameSnapshot;
        return $this;
    }

    public function getCategoryNameSnapshot(): ?string
    {
        return $this->categoryNameSnapshot;
    }

    public function setCategoryNameSnapshot(?string $categoryNameSnapshot): static
    {
        $this->categoryNameSnapshot = $categoryNameSnapshot;
        return $this;
    }

    public function getUserNameSnapshot(): ?string
    {
        return $this->userNameSnapshot;
    }

    public function setUserNameSnapshot(?string $userNameSnapshot): static
    {
        $this->userNameSnapshot = $userNameSnapshot;
        return $this;
    }
}
