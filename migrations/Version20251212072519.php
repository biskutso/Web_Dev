<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251212072519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_log CHANGE username username VARCHAR(255) NOT NULL, CHANGE role role VARCHAR(255) NOT NULL, CHANGE target_data target_data VARCHAR(255) NOT NULL, CHANGE datetime datetime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEDE18E50B');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEDE18E50B FOREIGN KEY (product_id_id) REFERENCES products (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_log CHANGE username username VARCHAR(255) DEFAULT NULL, CHANGE role role VARCHAR(255) DEFAULT NULL, CHANGE target_data target_data VARCHAR(255) DEFAULT NULL, CHANGE datetime datetime DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEDE18E50B');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEDE18E50B FOREIGN KEY (product_id_id) REFERENCES products (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
