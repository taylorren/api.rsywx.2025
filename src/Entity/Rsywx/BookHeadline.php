<?php

namespace App\Entity\Rsywx;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\Rsywx\BookHeadlineRepository")]
#[ORM\Table(name: "book_headline")]
#[ApiResource]
class BookHeadline
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "hid")]
    private ?int $hid = null;

    #[ORM\OneToOne(targetEntity: BookBook::class)]
    #[ORM\JoinColumn(name: "bid", referencedColumnName: "id", unique: true)]
    private ?BookBook $book = null;

    #[ORM\Column(type: "string", length: 100)]
    private ?string $reviewtitle = null;

    #[ORM\Column(type: "date", name: "create_at")]
    private ?\DateTimeInterface $createAt = null;

    #[ORM\Column(type: "boolean")]
    private ?bool $display = null;

    // Getters and Setters
    public function getHid(): ?int
    {
        return $this->hid;
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

    public function getReviewtitle(): ?string
    {
        return $this->reviewtitle;
    }

    public function setReviewtitle(string $reviewtitle): self
    {
        $this->reviewtitle = $reviewtitle;
        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): self
    {
        $this->createAt = $createAt;
        return $this;
    }

    public function isDisplay(): ?bool
    {
        return $this->display;
    }

    public function setDisplay(bool $display): self
    {
        $this->display = $display;
        return $this;
    }
}