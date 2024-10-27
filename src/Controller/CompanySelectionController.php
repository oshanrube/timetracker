<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CompanySelectionController extends AbstractController
{
    #[Route('/company/selection', name: 'app_company_selection')]
    public function index(): Response
    {
        return $this->render('company_selection/index.html.twig', [
            'controller_name' => 'CompanySelectionController',
            'error' => ""
        ]);
    }
}
