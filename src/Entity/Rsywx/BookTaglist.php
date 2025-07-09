<?php

namespace App\Entity\Rsywx;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\Rsywx\BookTaglistRepository")]
#[ORM\Table(name: "book_taglist")]
#[ApiResource]
class BookTaglist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint", name: "tid")]
    private ?int $tid = null;

    #[ORM\ManyToOne(targetEntity: BookBook::class)]
    #[ORM\JoinColumn(name: "bid", referencedColumnName: "id")]
    private ?BookBook $book = null;

    #[ORM\Column(type: "string", length: 20)]
    private ?string $tag = null;

    // Getters and Setters
    public function getTid(): ?int
    {
        return $this->tid;
    }

    public function getBook(): ?BookBook
    {
        return $this->book;
    }

    public function setBook(?BookBook $book): self
    {
        $this->book = $book;
        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;
        return $this;
    }
}