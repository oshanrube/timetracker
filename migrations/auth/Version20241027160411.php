<?php

declare(strict_types=1);

namespace DoctrineMigrations\Auth;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241027160411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE LoginHasCompany (id INT AUTO_INCREMENT NOT NULL, Login_id INT NOT NULL, Company_id INT DEFAULT NULL, INDEX IDX_BF89766DA5C4820B (Login_id), INDEX IDX_BF89766DD8C61906 (Company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE LoginHasCompany ADD CONSTRAINT FK_BF89766DA5C4820B FOREIGN KEY (Login_id) REFERENCES Login (id)');
        $this->addSql('ALTER TABLE LoginHasCompany ADD CONSTRAINT FK_BF89766DD8C61906 FOREIGN KEY (Company_id) REFERENCES CompanyHasSubdomain (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE LoginHasCompany DROP FOREIGN KEY FK_BF89766DA5C4820B');
        $this->addSql('ALTER TABLE LoginHasCompany DROP FOREIGN KEY FK_BF89766DD8C61906');
        $this->addSql('DROP TABLE LoginHasCompany');
    }
}
