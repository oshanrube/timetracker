<?php

namespace App\Tests\CorePages;

use App\Tests\FunctionalTests\BaseLoggedInUser;

class CompanySelectionControllerTest extends BaseLoggedInUser
{

    public function testCreatingNewCompany(): void
    {
        // go to page
        $this->client->request('GET', '/company/selection');
        self::assertResponseIsSuccessful();
        self::assertPageTitleContains($this->translator->trans('Select Company'));

        // submit the form
        $this->client->submitForm($this->translator->trans('Create new company'), [
            'create_company[name]'        => 'Test company',
            'create_company[subdomain]'   => 'test-company',
            'create_company[description]' => "Test description",
        ]);

        self::assertPageTitleContains($this->translator->trans('Select Company'));
        // ensure the company is created
        self::assertSelectorTextContains('#company-list > a:nth-child(1)', 'Test company');

        $this->client->clickLink("Test company");
        self::assertResponseRedirects('/dashboard');
        $this->client->followRedirect();
        self::assertPageTitleContains($this->translator->trans('Welcome to Timetracker!'));

        self::assertSelectorNotExists('.alert-danger');
        self::assertResponseIsSuccessful();
    }
}
