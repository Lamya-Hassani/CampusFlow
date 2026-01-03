<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260103115859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // columns already exist in DB, do not add them again:
        // $this->addSql('ALTER TABLE grade ADD teacher_id INT NOT NULL');
        // $this->addSql('ALTER TABLE grade ADD classe_id INT NOT NULL');

        // if you don't yet have FK + indexes, keep these lines.
        // If those also already exist, you can comment them too.
        $this->addSql('ALTER TABLE grade ADD CONSTRAINT FK_595AAE3441807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id)');
        $this->addSql('ALTER TABLE grade ADD CONSTRAINT FK_595AAE341F55203D FOREIGN KEY (classe_id) REFERENCES classe (id)');
        $this->addSql('CREATE INDEX IDX_595AAE3441807E1D ON grade (teacher_id)');
        $this->addSql('CREATE INDEX IDX_595AAE341F55203D ON grade (classe_id)');
    }


    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grade DROP FOREIGN KEY FK_595AAE3441807E1D');
        $this->addSql('ALTER TABLE grade DROP FOREIGN KEY FK_595AAE348F5EA509');
        $this->addSql('DROP INDEX IDX_595AAE3441807E1D ON grade');
        $this->addSql('DROP INDEX IDX_595AAE348F5EA509 ON grade');
        $this->addSql('ALTER TABLE grade DROP teacher_id, DROP classe_id');
    }
}
