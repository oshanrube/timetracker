<?php

namespace App\Entity\Auth;

use App\Repository\Auth\CompanyHasSubdomainRepository;
use App\Services\DomainNameSanitizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?int $id = NULL;

    #[ORM\Column(length: 255, unique: TRUE)]
    private ?string $subdomain = NULL;

    /**
     * @var Collection<int, LoginHasCompany>
     */
    #[ORM\OneToMany(targetEntity: LoginHasCompany::class, mappedBy: 'Company')]
    private Collection $loginHasCompanies;

    #[ORM\Column(length: 255, nullable: TRUE)]
    private ?string $name = NULL;

    public function __construct()
    {
        $this->loginHasCompanies = new ArrayCollection();
    }

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
        $this->subdomain = DomainNameSanitizer::sanitize($subdomain);

        return $this;
    }

    /**
     * @return Collection<int, LoginHasCompany>
     */
    public function getLoginHasCompanies(): Collection
    {
        return $this->loginHasCompanies;
    }

    public function addLoginHasCompany(LoginHasCompany $loginHasCompany): static
    {
        if (!$this->loginHasCompanies->contains($loginHasCompany)) {
            $this->loginHasCompanies->add($loginHasCompany);
            $loginHasCompany->setCompany($this);
        }

        return $this;
    }

    public function removeLoginHasCompany(LoginHasCompany $loginHasCompany): static
    {
        if ($this->loginHasCompanies->removeElement($loginHasCompany)) {
            // set the owning side to null (unless already changed)
            if ($loginHasCompany->getCompany() === $this) {
                $loginHasCompany->setCompany(NULL);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
