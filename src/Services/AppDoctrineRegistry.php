<?php

namespace App\Services;

use Doctrine\Bundle\DoctrineBundle\Registry;

class AppDoctrineRegistry extends Registry
{
    public function getService($name): object
    {
        if ($name === "doctrine.orm.company_entity_manager"
            && $this->container->has('doctrine.orm.user_company_entity_manager')) {
            return $this->container->get('doctrine.orm.user_company_entity_manager');
        }

        return parent::getService($name);
    }
}