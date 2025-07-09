<?php

namespace App\Entity\Rsywx;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\Rsywx\BookVisitRepository")]
#[ORM\Table(name: "book_visit")]
#[ApiResource]
class BookVisit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "vid")]
    private ?int $vid = null;

    #[ORM\ManyToOne(targetEntity: BookBook::class)]
    #[ORM\JoinColumn(name: "bookid", referencedColumnName: "id")]
    private ?BookBook $book = null;

    #[ORM\Column(type: "datetime")]
    private ?\DateTimeInterface $visitwhen = null;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $region = null;

    // Getters and Setters
    public function getVid(): ?int
    {
        return $this->vid;
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

    public function getVisitwhen(): ?\DateTimeInterface
    {
        return $this->visitwhen;
    }

    public function setVisitwhen(\DateTimeInterface $visitwhen): self
    {
        $this->visitwhen = $visitwhen;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;
        return $this;
    }
}