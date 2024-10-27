<?php

namespace App\Services;

use App\Entity\Auth\CompanyHasSubdomain;
use App\Entity\Auth\Login;
use App\Entity\Auth\LoginHasCompany;
use App\Entity\Company\Company;
use App\Repository\Auth\CompanyHasSubdomainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CompanyCreation
{
    public function __construct(private CompanyHasSubdomainRepository $company_has_subdomain_repository,
    private ManagerRegistry $doctrine)
    {

    }
    public function checkSubdomainAvailability(string $subdomain): bool
    {
        return null !== $this->company_has_subdomain_repository->findOneBy(['subdomain' => $subdomain]);
    }
    public function createCompany(Company $company, Login $login): bool
    {
        try {
            $company_has_subdomain = new CompanyHasSubdomain();
            $company_has_subdomain->setSubdomain($company->getSubdomain());
            $this->doctrine->getManager('auth')->persist($company_has_subdomain);
            // create link for user
            $login_has_company = new LoginHasCompany();
            $login_has_company->setCompany($company_has_subdomain);
            $login_has_company->setLogin($login);
            $this->doctrine->getManager('auth')->persist($login_has_company);
            $this->doctrine->getManager('auth')->flush();

            $company->setId($company_has_subdomain->getId());
            $this->doctrine->getManager('company')->persist($company);
            $this->doctrine->getManager('company')->flush();
        } catch (\Exception $e) {
            //roll back
            return false;
        }
        return true;
    }
}