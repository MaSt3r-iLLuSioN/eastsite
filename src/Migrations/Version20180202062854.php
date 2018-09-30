<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180202062854 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE block_entity ADD listedpages LONGTEXT NOT NULL, ADD onpage TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE block_type_entity DROP title');
        $this->addSql('ALTER TABLE htmlblock_entity DROP title');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE block_entity DROP listedpages, DROP onpage');
        $this->addSql('ALTER TABLE block_type_entity ADD title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE htmlblock_entity ADD title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
