<?php

namespace App\Services;

use App\Entity\Auth\CompanyHasSubdomain;
use App\Entity\Auth\Login;
use App\Entity\Auth\LoginHasCompany;
use App\Entity\Company\Company;
use App\Services\DatabaseManager\DatabaseCreator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\VarDumper\Cloner\Data;

class CompanyCreation
{
    public function __construct(
        private readonly AppDoctrineRegistry $doctrine,
        private readonly DatabaseCreator     $database_creator,
    ) {

    }

    public function checkSubdomainAvailability(string $subdomain): bool
    {
        return NULL === $this->doctrine
                ->getRepository(CompanyHasSubdomain::class, 'auth')
                ->findOneBy(['subdomain' => $subdomain]);
    }

    public function createCompany(Company $company, ?UserInterface $login): bool
    {
        if (!$login instanceof Login) {
            return FALSE;
        }
        try {
            if ($company->getSubdomain() === NULL) {
                $company->setSubdomain(DomainNameSanitizer::sanitize($company->getName()));
            }
            $company_has_subdomain = new CompanyHasSubdomain();
            $company_has_subdomain->setSubdomain($company->getSubdomain());
            $company_has_subdomain->setName($company->getName());
            $this->doctrine->getManager('auth')->persist($company_has_subdomain);

            // create link for user
            $login_has_company = new LoginHasCompany();
            $login_has_company->setCompany($company_has_subdomain);
            $login_has_company->setLogin($login);
            $this->doctrine->getManager('auth')->persist($login_has_company);
            $this->doctrine->getManager('auth')->flush();
        } catch (UniqueConstraintViolationException $e) {
            return FALSE;
        }
        $company->setId($company_has_subdomain->getId());
        // load new Database
        $this->database_creator->loadDatabase($company->getId());
        // save to db
        $this->doctrine->getManager('company')->persist($company);
        $this->doctrine->getManager('company')->flush();

        return TRUE;
    }
}