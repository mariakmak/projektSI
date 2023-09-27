<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230916192957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Categories CHANGE author_id author_id INT NOT NULL');
        $this->addSql('ALTER TABLE transactions CHANGE author_id author_id INT NOT NULL');
        $this->addSql('ALTER TABLE wallets CHANGE author_id author_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Categories CHANGE author_id author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transactions CHANGE author_id author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE wallets CHANGE author_id author_id INT DEFAULT NULL');
    }
}
