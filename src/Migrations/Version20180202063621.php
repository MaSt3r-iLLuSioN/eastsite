<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180202063621 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE page_entity (id INT AUTO_INCREMENT NOT NULL, layout_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_61FEB2768C22AA1A (layout_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page_entity ADD CONSTRAINT FK_61FEB2768C22AA1A FOREIGN KEY (layout_id) REFERENCES layout_entity (id)');
        $this->addSql('ALTER TABLE blog ADD content LONGTEXT NOT NULL, DROP description');
        $this->addSql('ALTER TABLE htmlblock_entity CHANGE content content LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE tab CHANGE content content LONGTEXT NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE page_entity');
        $this->addSql('ALTER TABLE blog ADD description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP content');
        $this->addSql('ALTER TABLE htmlblock_entity CHANGE content content VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE tab CHANGE content content VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
