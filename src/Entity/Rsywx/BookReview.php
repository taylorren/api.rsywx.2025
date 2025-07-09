<?php

namespace App\Entity\Rsywx;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\Rsywx\BookReviewRepository")]
#[ORM\Table(name: "book_review")]
#[ApiResource]
class BookReview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BookHeadline::class)]
    #[ORM\JoinColumn(name: "hid", referencedColumnName: "hid")]
    private ?BookHeadline $headline = null;

    #[ORM\Column(type: "string", length: 100)]
    private ?string $title = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $datein = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $uri = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $feature = null;

    #[ORM\Column(type: "string", length: 45, nullable: true, name: "book_reviewcol")]
    private ?string $bookReviewcol = null;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeadline(): ?BookHeadline
    {
        return $this->headline;
    }

    public function setHeadline(?BookHeadline $headline): self
    {
        $this->headline = $headline;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDatein(): ?\DateTimeInterface
    {
        return $this->datein;
    }

    public function setDatein(\DateTimeInterface $datein): self
    {
        $this->datein = $datein;
        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;
        return $this;
    }

    public function getFeature(): ?string
    {
        return $this->feature;
    }

    public function setFeature(?string $feature): self
    {
        $this->feature = $feature;
        return $this;
    }

    public function getBookReviewcol(): ?string
    {
        return $this->bookReviewcol;
    }

    public function setBookReviewcol(?string $bookReviewcol): self
    {
        $this->bookReviewcol = $bookReviewcol;
        return $this;
    }
}