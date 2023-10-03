<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231003131741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX email_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Category ADD author_id INT NOT NULL');
        $this->addSql('ALTER TABLE Category ADD CONSTRAINT FK_FF3A7B97F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_FF3A7B97F675F31B ON Category (author_id)');
        $this->addSql('ALTER TABLE transactions ADD author_id INT NOT NULL, ADD name VARCHAR(255) NOT NULL, CHANGE currency_id currency_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C12469DE2 FOREIGN KEY (category_id) REFERENCES Category (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C38248176 FOREIGN KEY (currency_id) REFERENCES currencies (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C712520F3 FOREIGN KEY (wallet_id) REFERENCES wallets (id)');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_EAA81A4CF675F31B ON transactions (author_id)');
        $this->addSql('ALTER TABLE wallets ADD author_id INT NOT NULL, ADD sum INT NOT NULL');
        $this->addSql('ALTER TABLE wallets ADD CONSTRAINT FK_967AAA6CF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_967AAA6CF675F31B ON wallets (author_id)');
        $this->addSql('ALTER TABLE wallet_currency ADD CONSTRAINT FK_9DF33C4B712520F3 FOREIGN KEY (wallet_id) REFERENCES wallets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wallet_currency ADD CONSTRAINT FK_9DF33C4B38248176 FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Category DROP FOREIGN KEY FK_FF3A7B97F675F31B');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4CF675F31B');
        $this->addSql('ALTER TABLE wallets DROP FOREIGN KEY FK_967AAA6CF675F31B');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP INDEX IDX_FF3A7B97F675F31B ON Category');
        $this->addSql('ALTER TABLE Category DROP author_id');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C12469DE2');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C38248176');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C712520F3');
        $this->addSql('DROP INDEX IDX_EAA81A4CF675F31B ON transactions');
        $this->addSql('ALTER TABLE transactions DROP author_id, DROP name, CHANGE currency_id currency_id INT NOT NULL');
        $this->addSql('ALTER TABLE wallet_currency DROP FOREIGN KEY FK_9DF33C4B712520F3');
        $this->addSql('ALTER TABLE wallet_currency DROP FOREIGN KEY FK_9DF33C4B38248176');
        $this->addSql('DROP INDEX IDX_967AAA6CF675F31B ON wallets');
        $this->addSql('ALTER TABLE wallets DROP author_id, DROP sum');
    }
}
