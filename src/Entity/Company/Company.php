<?php

namespace App\Entity\Company;

use App\Repository\Company\CompanyRepository;
use App\Services\DomainNameSanitizer;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: FALSE, hardDelete: TRUE)]
class Company
{
    use SoftDeleteableEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\Column]
    private ?int $id = NULL;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    private ?string $name = NULL;

    #[ORM\Column(type: Types::TEXT, nullable: TRUE)]
    private ?string $description = NULL;

    #[NotBlank]
    private ?string $subdomain = NULL;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSubdomain(): ?string
    {
        return $this->subdomain;
    }

    public function setSubdomain(?string $subdomain): void
    {
        if ($subdomain === NULL) {
            $subdomain = DomainNameSanitizer::sanitize($this->name);
        }

        $this->subdomain = DomainNameSanitizer::sanitize($subdomain);
    }

}
