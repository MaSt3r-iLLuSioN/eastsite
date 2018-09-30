<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180204205104 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE layout_regions (layout_entity_id INT NOT NULL, region_entity_id INT NOT NULL, INDEX IDX_D01B26613986ABDC (layout_entity_id), INDEX IDX_D01B2661D2E52969 (region_entity_id), PRIMARY KEY(layout_entity_id, region_entity_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE layout_regions ADD CONSTRAINT FK_D01B26613986ABDC FOREIGN KEY (layout_entity_id) REFERENCES layout_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE layout_regions ADD CONSTRAINT FK_D01B2661D2E52969 FOREIGN KEY (region_entity_id) REFERENCES region_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE region_entity DROP FOREIGN KEY FK_1DFCB83D8C22AA1A');
        $this->addSql('DROP INDEX IDX_1DFCB83D8C22AA1A ON region_entity');
        $this->addSql('ALTER TABLE region_entity DROP layout_id');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE layout_regions');
        $this->addSql('ALTER TABLE region_entity ADD layout_id INT NOT NULL');
        $this->addSql('ALTER TABLE region_entity ADD CONSTRAINT FK_1DFCB83D8C22AA1A FOREIGN KEY (layout_id) REFERENCES layout_entity (id)');
        $this->addSql('CREATE INDEX IDX_1DFCB83D8C22AA1A ON region_entity (layout_id)');
    }
}
