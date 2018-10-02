<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181001214308 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE gallery_item DROP FOREIGN KEY FK_8C040D924E7AF8F');
        $this->addSql('DROP TABLE blog_files');
        $this->addSql('DROP TABLE carousel_slideshow');
        $this->addSql('DROP TABLE gallery');
        $this->addSql('DROP TABLE gallery_item');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE newsletter_entity');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE blog_files (blog_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_F6DA0391DAE07E97 (blog_id), INDEX IDX_F6DA039193CB796C (file_id), PRIMARY KEY(blog_id, file_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carousel_slideshow (id INT AUTO_INCREMENT NOT NULL, block_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_9A0EA9C8E9ED820C (block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gallery (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gallery_item (id INT AUTO_INCREMENT NOT NULL, gallery_id INT NOT NULL, title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, img VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_8C040D924E7AF8F (gallery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, from_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, messge VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX UNIQ_B6BD307F78CED90B (from_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE newsletter_entity (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blog_files ADD CONSTRAINT FK_F6DA039193CB796C FOREIGN KEY (file_id) REFERENCES file_entity (id)');
        $this->addSql('ALTER TABLE blog_files ADD CONSTRAINT FK_F6DA0391DAE07E97 FOREIGN KEY (blog_id) REFERENCES base_entity (id)');
        $this->addSql('ALTER TABLE carousel_slideshow ADD CONSTRAINT FK_9A0EA9C8E9ED820C FOREIGN KEY (block_id) REFERENCES block_entity (id)');
        $this->addSql('ALTER TABLE gallery_item ADD CONSTRAINT FK_8C040D924E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F78CED90B FOREIGN KEY (from_id) REFERENCES user (id)');
    }
}
