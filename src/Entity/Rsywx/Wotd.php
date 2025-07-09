<?php

namespace App\Entity\Rsywx;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\Rsywx\WotdRepository")]
#[ORM\Table(name: "wotd")]
#[ApiResource]
class Wotd
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $word = null;

    #[ORM\Column(type: "string", length: 200, nullable: true)]
    private ?string $meaning = null;

    #[ORM\Column(type: "string", length: 300, nullable: true)]
    private ?string $sentence = null;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $type = null;

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

    public function getMeaning(): ?string
    {
        return $this->meaning;
    }

    public function setMeaning(?string $meaning): self
    {
        $this->meaning = $meaning;
        return $this;
    }

    public function getSentence(): ?string
    {
        return $this->sentence;
    }

    public function setSentence(?string $sentence): self
    {
        $this->sentence = $sentence;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }
}