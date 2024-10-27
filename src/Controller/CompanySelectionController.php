<?php

namespace App\Controller;

use App\Entity\Auth\Login;
use App\Entity\Company\Company;
use App\Form\CreateCompanyType;
use App\Form\RegistrationFormType;
use App\Repository\Auth\LoginHasCompanyRepository;
use App\Services\CompanyCreation;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class CompanySelectionController extends AbstractController
{
    #[Route('/company/selection', name: 'app_company_selection')]
    public function index(
        Request                   $request,
        CompanyCreation           $company_creation,
        LoginHasCompanyRepository $login_has_company_repository,
    ): Response {

        $company = new Company();
        $form    = $this->createForm(CreateCompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // check
            if ($company_creation->checkSubdomainAvailability($company->getSubdomain())) {
                $error = "";
            } else {
                //
                $company_creation->createCompany($company, $this->getUser());
            }
        }
        $LoginHasCompanies = $login_has_company_repository
            ->findBy(['Login' => $this->getUser()]);
        return $this->render('company_selection/index.html.twig', [
            'companyCreationForm' => $form,
            'controller_name'     => 'CompanySelectionController',
            'error'               => "",
            'LoginHasCompanies'           => $LoginHasCompanies,
        ]);
    }
}
