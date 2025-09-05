<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250822181000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX unique_category_name_per_user ON categories (name, author_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_currency_name ON currencies (name)');
        $this->addSql('CREATE UNIQUE INDEX unique_transaction_name_per_user ON transactions (name, author_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_wallet_name_per_user ON wallets (name, author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_transaction_name_per_user ON transactions');
        $this->addSql('DROP INDEX unique_category_name_per_user ON categories');
        $this->addSql('DROP INDEX unique_currency_name ON currencies');
        $this->addSql('DROP INDEX unique_wallet_name_per_user ON wallets');
    }
}
