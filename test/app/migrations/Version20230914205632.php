<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230914205632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Categories ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Categories ADD CONSTRAINT FK_75AE45B8F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_75AE45B8F675F31B ON Categories (author_id)');
        $this->addSql('ALTER TABLE transactions ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_EAA81A4CF675F31B ON transactions (author_id)');
        $this->addSql('ALTER TABLE wallets ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE wallets ADD CONSTRAINT FK_967AAA6CF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_967AAA6CF675F31B ON wallets (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Categories DROP FOREIGN KEY FK_75AE45B8F675F31B');
        $this->addSql('DROP INDEX IDX_75AE45B8F675F31B ON Categories');
        $this->addSql('ALTER TABLE Categories DROP author_id');
        $this->addSql('ALTER TABLE wallets DROP FOREIGN KEY FK_967AAA6CF675F31B');
        $this->addSql('DROP INDEX IDX_967AAA6CF675F31B ON wallets');
        $this->addSql('ALTER TABLE wallets DROP author_id');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4CF675F31B');
        $this->addSql('DROP INDEX IDX_EAA81A4CF675F31B ON transactions');
        $this->addSql('ALTER TABLE transactions DROP author_id');
    }
}
