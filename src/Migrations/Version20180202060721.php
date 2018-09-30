<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180202060721 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE block_entity (id INT AUTO_INCREMENT NOT NULL, region_id INT NOT NULL, type_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, priority INT NOT NULL, INDEX IDX_478612E398260155 (region_id), UNIQUE INDEX UNIQ_478612E3C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE block_type_entity (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE htmlblock_entity (id INT AUTO_INCREMENT NOT NULL, block_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B9F4AE09E9ED820C (block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE layout_entity (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region_entity (id INT AUTO_INCREMENT NOT NULL, layout_id INT NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_1DFCB83D8C22AA1A (layout_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE block_entity ADD CONSTRAINT FK_478612E398260155 FOREIGN KEY (region_id) REFERENCES region_entity (id)');
        $this->addSql('ALTER TABLE block_entity ADD CONSTRAINT FK_478612E3C54C8C93 FOREIGN KEY (type_id) REFERENCES block_type_entity (id)');
        $this->addSql('ALTER TABLE htmlblock_entity ADD CONSTRAINT FK_B9F4AE09E9ED820C FOREIGN KEY (block_id) REFERENCES block_entity (id)');
        $this->addSql('ALTER TABLE region_entity ADD CONSTRAINT FK_1DFCB83D8C22AA1A FOREIGN KEY (layout_id) REFERENCES layout_entity (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE htmlblock_entity DROP FOREIGN KEY FK_B9F4AE09E9ED820C');
        $this->addSql('ALTER TABLE block_entity DROP FOREIGN KEY FK_478612E3C54C8C93');
        $this->addSql('ALTER TABLE region_entity DROP FOREIGN KEY FK_1DFCB83D8C22AA1A');
        $this->addSql('ALTER TABLE block_entity DROP FOREIGN KEY FK_478612E398260155');
        $this->addSql('DROP TABLE block_entity');
        $this->addSql('DROP TABLE block_type_entity');
        $this->addSql('DROP TABLE htmlblock_entity');
        $this->addSql('DROP TABLE layout_entity');
        $this->addSql('DROP TABLE region_entity');
    }
}
