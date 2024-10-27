<?php

namespace App\Entity\Auth;

use App\Repository\Auth\LoginHasCompanyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoginHasCompanyRepository::class)]
class LoginHasCompany
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'loginHasCompanies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Login $Login = null;

    #[ORM\ManyToOne(inversedBy: 'loginHasCompanies')]
    private ?CompanyHasSubdomain $Company = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?Login
    {
        return $this->Login;
    }

    public function setLogin(?Login $Login): static
    {
        $this->Login = $Login;

        return $this;
    }

    public function getCompany(): ?CompanyHasSubdomain
    {
        return $this->Company;
    }

    public function setCompany(?CompanyHasSubdomain $Company): static
    {
        $this->Company = $Company;

        return $this;
    }
}
