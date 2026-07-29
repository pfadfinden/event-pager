<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260728080357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nullable recipient_id link from user to message_recipient';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD recipient_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649E92F8F78 FOREIGN KEY (recipient_id) REFERENCES message_recipient (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_8D93D649E92F8F78 ON "user" (recipient_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649E92F8F78');
        $this->addSql('DROP INDEX IDX_8D93D649E92F8F78');
        $this->addSql('ALTER TABLE "user" DROP recipient_id');
    }
}
