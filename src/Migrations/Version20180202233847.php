<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180202233847 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE page_entity DROP INDEX UNIQ_61FEB2768C22AA1A, ADD INDEX IDX_61FEB2768C22AA1A (layout_id)');
        $this->addSql('ALTER TABLE page_entity CHANGE layout_id layout_id INT NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE page_entity DROP INDEX IDX_61FEB2768C22AA1A, ADD UNIQUE INDEX UNIQ_61FEB2768C22AA1A (layout_id)');
        $this->addSql('ALTER TABLE page_entity CHANGE layout_id layout_id INT DEFAULT NULL');
    }
}
