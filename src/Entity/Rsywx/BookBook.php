<?php

namespace App\Entity\Rsywx;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\Rsywx\BookBookRepository")]
#[ORM\Table(name: "book_book")]
#[ApiResource]
class BookBook
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BookPlace::class)]
    #[ORM\JoinColumn(name: "place", referencedColumnName: "id")]
    private ?BookPlace $place = null;

    #[ORM\ManyToOne(targetEntity: BookPublisher::class)]
    #[ORM\JoinColumn(name: "publisher", referencedColumnName: "id")]
    private ?BookPublisher $publisher = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private ?string $bookid = null;

    #[ORM\Column(type: "string", length: 200)]
    private ?string $title = null;

    #[ORM\Column(type: "string", length: 200)]
    private ?string $author = null;

    #[ORM\Column(type: "string", length: 40)]
    private ?string $region = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $copyrighter = null;

    #[ORM\Column(type: "boolean")]
    private ?bool $translated = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $purchdate = null;

    #[ORM\Column(type: "float", precision: 22, scale: 2)]
    private ?float $price = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $pubdate = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $printdate = null;

    #[ORM\Column(type: "string", length: 5)]
    private ?string $ver = null;

    #[ORM\Column(type: "string", length: 6)]
    private ?string $deco = null;

    #[ORM\Column(type: "integer")]
    private ?int $kword = null;

    #[ORM\Column(type: "integer")]
    private ?int $page = null;

    #[ORM\Column(type: "string", length: 17)]
    private ?string $isbn = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: "string", length: 2, nullable: true)]
    private ?string $ol = null;

    #[ORM\Column(type: "text")]
    private ?string $intro = null;

    #[ORM\Column(type: "boolean")]
    private ?bool $instock = null;

    #[ORM\Column(type: "string", length: 3, nullable: true)]
    private ?string $location = null;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlace(): ?BookPlace
    {
        return $this->place;
    }

    public function setPlace(?BookPlace $place): self
    {
        $this->place = $place;
        return $this;
    }

    public function getPublisher(): ?BookPublisher
    {
        return $this->publisher;
    }

    public function setPublisher(?BookPublisher $publisher): self
    {
        $this->publisher = $publisher;
        return $this;
    }

    public function getBookid(): ?string
    {
        return $this->bookid;
    }

    public function setBookid(string $bookid): self
    {
        $this->bookid = $bookid;
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;
        return $this;
    }

    public function getCopyrighter(): ?string
    {
        return $this->copyrighter;
    }

    public function setCopyrighter(?string $copyrighter): self
    {
        $this->copyrighter = $copyrighter;
        return $this;
    }

    public function isTranslated(): ?bool
    {
        return $this->translated;
    }

    public function setTranslated(bool $translated): self
    {
        $this->translated = $translated;
        return $this;
    }

    public function getPurchdate(): ?\DateTimeInterface
    {
        return $this->purchdate;
    }

    public function setPurchdate(\DateTimeInterface $purchdate): self
    {
        $this->purchdate = $purchdate;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getPubdate(): ?\DateTimeInterface
    {
        return $this->pubdate;
    }

    public function setPubdate(\DateTimeInterface $pubdate): self
    {
        $this->pubdate = $pubdate;
        return $this;
    }

    public function getPrintdate(): ?\DateTimeInterface
    {
        return $this->printdate;
    }

    public function setPrintdate(\DateTimeInterface $printdate): self
    {
        $this->printdate = $printdate;
        return $this;
    }

    public function getVer(): ?string
    {
        return $this->ver;
    }

    public function setVer(string $ver): self
    {
        $this->ver = $ver;
        return $this;
    }

    public function getDeco(): ?string
    {
        return $this->deco;
    }

    public function setDeco(string $deco): self
    {
        $this->deco = $deco;
        return $this;
    }

    public function getKword(): ?int
    {
        return $this->kword;
    }

    public function setKword(int $kword): self
    {
        $this->kword = $kword;
        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getOl(): ?string
    {
        return $this->ol;
    }

    public function setOl(?string $ol): self
    {
        $this->ol = $ol;
        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(string $intro): self
    {
        $this->intro = $intro;
        return $this;
    }

    public function isInstock(): ?bool
    {
        return $this->instock;
    }

    public function setInstock(bool $instock): self
    {
        $this->instock = $instock;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }
}