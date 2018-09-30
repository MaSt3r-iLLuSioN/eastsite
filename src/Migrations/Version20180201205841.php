<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180201205841 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE config ADD about VARCHAR(255) NOT NULL, ADD home VARCHAR(255) NOT NULL, ADD slogan VARCHAR(255) NOT NULL, ADD mission VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event ADD title VARCHAR(255) NOT NULL, ADD description VARCHAR(255) NOT NULL, ADD datestart DATETIME NOT NULL, ADD dateend DATETIME NOT NULL');
        $this->addSql('ALTER TABLE gallery ADD title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE gallery_item ADD gallery_id INT NOT NULL, ADD title VARCHAR(255) NOT NULL, ADD description VARCHAR(255) NOT NULL, ADD img VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE gallery_item ADD CONSTRAINT FK_8C040D924E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id)');
        $this->addSql('CREATE INDEX IDX_8C040D924E7AF8F ON gallery_item (gallery_id)');
        $this->addSql('ALTER TABLE message ADD subject VARCHAR(255) NOT NULL, ADD messge VARCHAR(255) NOT NULL, ADD `from` VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tab ADD tab_id INT NOT NULL, ADD title VARCHAR(255) NOT NULL, ADD content VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tab ADD CONSTRAINT FK_73E3430C8D0C9323 FOREIGN KEY (tab_id) REFERENCES tab_list (id)');
        $this->addSql('CREATE INDEX IDX_73E3430C8D0C9323 ON tab (tab_id)');
        $this->addSql('ALTER TABLE tab_list ADD title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE testimonial ADD author INT NOT NULL, ADD content VARCHAR(255) NOT NULL, ADD date DATETIME NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE config DROP about, DROP home, DROP slogan, DROP mission');
        $this->addSql('ALTER TABLE event DROP title, DROP description, DROP datestart, DROP dateend');
        $this->addSql('ALTER TABLE gallery DROP title');
        $this->addSql('ALTER TABLE gallery_item DROP FOREIGN KEY FK_8C040D924E7AF8F');
        $this->addSql('DROP INDEX IDX_8C040D924E7AF8F ON gallery_item');
        $this->addSql('ALTER TABLE gallery_item DROP gallery_id, DROP title, DROP description, DROP img');
        $this->addSql('ALTER TABLE message DROP subject, DROP messge, DROP `from`');
        $this->addSql('ALTER TABLE tab DROP FOREIGN KEY FK_73E3430C8D0C9323');
        $this->addSql('DROP INDEX IDX_73E3430C8D0C9323 ON tab');
        $this->addSql('ALTER TABLE tab DROP tab_id, DROP title, DROP content');
        $this->addSql('ALTER TABLE tab_list DROP title');
        $this->addSql('ALTER TABLE testimonial DROP author, DROP content, DROP date');
    }
}
