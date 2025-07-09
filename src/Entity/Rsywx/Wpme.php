<?php

namespace App\Entity\Rsywx;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\Rsywx\WpmeRepository")]
#[ORM\Table(name: "wpme")]
#[ApiResource]
class Wpme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $word = null;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $root = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $datein = null;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: "text", nullable: true, name: "desc")]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $epro = null;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $apro = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $sample = null;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(?string $word): self
    {
        $this->word = $word;
        return $this;
    }

    public function getRoot(): ?string
    {
        return $this->root;
    }

    public function setRoot(?string $root): self
    {
        $this->root = $root;
        return $this;
    }

    public function getDatein(): ?\DateTimeInterface
    {
        return $this->datein;
    }

    public function setDatein(?\DateTimeInterface $datein): self
    {
        $this->datein = $datein;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getEpro(): ?string
    {
        return $this->epro;
    }

    public function setEpro(?string $epro): self
    {
        $this->epro = $epro;
        return $this;
    }

    public function getApro(): ?string
    {
        return $this->apro;
    }

    public function setApro(?string $apro): self
    {
        $this->apro = $apro;
        return $this;
    }

    public function getSample(): ?string
    {
        return $this->sample;
    }

    public function setSample(?string $sample): self
    {
        $this->sample = $sample;
        return $this;
    }
}