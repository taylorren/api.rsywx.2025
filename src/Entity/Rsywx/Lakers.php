<?php

namespace App\Entity\Rsywx;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\Rsywx\LakersRepository")]
#[ORM\Table(name: "lakers")]
#[ApiResource]
class Lakers
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private ?int $season = null;

    #[ORM\Column(type: "string", length: 10)]
    private ?string $team = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $dateplayed = null;

    #[ORM\Column(type: "integer")]
    private ?int $selfscore = null;

    #[ORM\Column(type: "integer")]
    private ?int $oppscore = null;

    #[ORM\Column(type: "string", length: 1, nullable: true)]
    private ?string $winlose = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $remark = null;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): self
    {
        $this->season = $season;
        return $this;
    }

    public function getTeam(): ?string
    {
        return $this->team;
    }

    public function setTeam(string $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function getDateplayed(): ?\DateTimeInterface
    {
        return $this->dateplayed;
    }

    public function setDateplayed(\DateTimeInterface $dateplayed): self
    {
        $this->dateplayed = $dateplayed;
        return $this;
    }

    public function getSelfscore(): ?int
    {
        return $this->selfscore;
    }

    public function setSelfscore(int $selfscore): self
    {
        $this->selfscore = $selfscore;
        return $this;
    }

    public function getOppscore(): ?int
    {
        return $this->oppscore;
    }

    public function setOppscore(int $oppscore): self
    {
        $this->oppscore = $oppscore;
        return $this;
    }

    public function getWinlose(): ?string
    {
        return $this->winlose;
    }

    public function setWinlose(?string $winlose): self
    {
        $this->winlose = $winlose;
        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;
        return $this;
    }
}