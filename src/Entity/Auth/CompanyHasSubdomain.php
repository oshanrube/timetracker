<?php

namespace App\Entity\Auth;

use App\Repository\Auth\CompanyHasSubdomainRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CompanyHasSubdomainRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_SUBDOMAIN', fields: ['subdomain'])]
#[UniqueEntity(fields: ['subdomain'], message: 'There is already an account with this subdomain')]

class CompanyHasSubdomain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $subdomain = null;

    #[ORM\Column]
    private ?int $companyId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubdomain(): ?string
    {
        return $this->subdomain;
    }

    public function setSubdomain(string $subdomain): static
    {
        $this->subdomain = $subdomain;

        return $this;
    }

    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): static
    {
        $this->companyId = $companyId;

        return $this;
    }
}
