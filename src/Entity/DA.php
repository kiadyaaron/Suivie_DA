<?php

namespace App\Entity;

use App\Repository\DARepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: DARepository::class)]
class DA
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ReferenceDA = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $DateCreationDA = null;

    #[ORM\Column(length: 255)]
    private ?string $EtatDA = null;

    #[ORM\Column(length: 255)]
    private ?string $Article = null;

    #[ORM\Column(length: 255)]
    private ?string $ChantierDepartement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ReferenceBCA = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $CreationBCA = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Fournisseur = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $DateLivraison = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $RetardDABCA = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $RetardLivraison = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferenceDA(): ?string
    {
        return $this->ReferenceDA;
    }

    public function setReferenceDA(string $ReferenceDA): static
    {
        $this->ReferenceDA = $ReferenceDA;
        return $this;
    }

    public function getDateCreationDA(): ?\DateTimeInterface
    {
        return $this->DateCreationDA;
    }

    public function setDateCreationDA(\DateTimeInterface $DateCreationDA): static
    {
        $this->DateCreationDA = $DateCreationDA;
        return $this;
    }

    public function getEtatDA(): ?string
    {
        return $this->EtatDA;
    }

    public function setEtatDA(string $EtatDA): static
    {
        $this->EtatDA = $EtatDA;
        return $this;
    }

    public function getArticle(): ?string
    {
        return $this->Article;
    }

    public function setArticle(string $Article): static
    {
        $this->Article = $Article;
        return $this;
    }

    public function getChantierDepartement(): ?string
    {
        return $this->ChantierDepartement;
    }

    public function setChantierDepartement(string $ChantierDepartement): static
    {
        $this->ChantierDepartement = $ChantierDepartement;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): static
    {
        $this->Description = $Description;
        return $this;
    }

    public function getReferenceBCA(): ?string
    {
        return $this->ReferenceBCA;
    }

    public function setReferenceBCA(?string $ReferenceBCA): static
    {
        $this->ReferenceBCA = $ReferenceBCA;
        return $this;
    }

    public function getCreationBCA(): ?\DateTimeInterface
    {
        return $this->CreationBCA;
    }

    public function setCreationBCA(?\DateTimeInterface $CreationBCA): static
    {
        $this->CreationBCA = $CreationBCA;
        return $this;
    }

    public function getFournisseur(): ?string
    {
        return $this->Fournisseur;
    }

    public function setFournisseur(?string $Fournisseur): static
    {
        $this->Fournisseur = $Fournisseur;
        return $this;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->DateLivraison;
    }

    public function setDateLivraison(?\DateTimeInterface $DateLivraison): static
    {
        $this->DateLivraison = $DateLivraison;
        return $this;
    }

    public function getRetardDABCA(): ?int
    {
        return $this->RetardDABCA;
    }

    public function setRetardDABCA(?int $RetardDABCA): static
    {
        $this->RetardDABCA = $RetardDABCA;
        return $this;
    }

    public function getRetardLivraison(): ?int
    {
        return $this->RetardLivraison ?? 0;
    }

    public function setRetardLivraison(?int $RetardLivraison): static
    {
        $this->RetardLivraison = $RetardLivraison;
        return $this;
    }

   public function calculerRetards(): void
    {
         $today = new \DateTimeImmutable();

        if ($this->DateCreationDA && !$this->CreationBCA) {
            $this->RetardDABCA = $this->DateCreationDA->diff($today)->days;
        }

        if ($this->CreationBCA && !$this->DateLivraison) {
            $this->RetardLivraison = $this->CreationBCA->diff($today)->days;
        } 
    }


    #[ORM\PostLoad]
    public function updateRetardsAfterLoad(): void
    {
        $this->calculerRetards();
    }
}
