<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['inscription:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre_Even = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descri_Even = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu_Even = null;

    #[ORM\Column]
    private ?int $capacite_Even = null;

    #[ORM\Column(length: 255)]
    private ?string $categorie_Even = null;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Inscription::class)]
    #[Groups(['inscription:read'])]
    private $inscription;

    public function __construct()
    {
        $this->inscription = new ArrayCollection();
    }

    public function getInscription(): Collection
    {
        return $this->inscription;
    }

    public function addInscription(Inscription $inscription): self
{
    if (!$this->inscription->contains($inscription)) {
        $this->inscription[] = $inscription;
        $inscription->setEvenement($this);
    }

    return $this;
}

public function removeInscription(Inscription $inscription): self
{
    if ($this->inscription->removeElement($inscription)) {
        // set the owning side to null (unless already changed)
        if ($inscription->getEvenement() === $this) {
            $inscription->setEvenement(null);
        }
    }

    return $this;
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreEven(): ?string
    {
        return $this->titre_Even;
    }

    public function setTitreEven(string $titre_Even): static
    {
        $this->titre_Even = $titre_Even;

        return $this;
    }

    public function getDescriEven(): ?string
    {
        return $this->descri_Even;
    }

    public function setDescriEven(string $descri_Even): static
    {
        $this->descri_Even = $descri_Even;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getLieuEven(): ?string
    {
        return $this->lieu_Even;
    }

    public function setLieuEven(string $lieu_Even): static
    {
        $this->lieu_Even = $lieu_Even;

        return $this;
    }

    public function getCapaciteEven(): ?int
    {
        return $this->capacite_Even;
    }

    public function setCapaciteEven(int $capacite_Even): static
    {
        $this->capacite_Even = $capacite_Even;

        return $this;
    }

    public function getCategorieEven(): ?string
    {
        return $this->categorie_Even;
    }

    public function setCategorieEven(string $categorie_Even): static
    {
        $this->categorie_Even = $categorie_Even;

        return $this;
    }
}
