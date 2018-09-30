<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180201233852 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blog ADD author_id INT DEFAULT NULL, DROP author');
        $this->addSql('ALTER TABLE blog ADD CONSTRAINT FK_C0155143F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C0155143F675F31B ON blog (author_id)');
        $this->addSql('ALTER TABLE config ADD logo VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE message ADD from_id INT DEFAULT NULL, DROP `from`');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F78CED90B FOREIGN KEY (from_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6BD307F78CED90B ON message (from_id)');
        $this->addSql('ALTER TABLE testimonial ADD author_id INT DEFAULT NULL, DROP author');
        $this->addSql('ALTER TABLE testimonial ADD CONSTRAINT FK_E6BDCDF7F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E6BDCDF7F675F31B ON testimonial (author_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blog DROP FOREIGN KEY FK_C0155143F675F31B');
        $this->addSql('DROP INDEX UNIQ_C0155143F675F31B ON blog');
        $this->addSql('ALTER TABLE blog ADD author INT NOT NULL, DROP author_id');
        $this->addSql('ALTER TABLE config DROP logo');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F78CED90B');
        $this->addSql('DROP INDEX UNIQ_B6BD307F78CED90B ON message');
        $this->addSql('ALTER TABLE message ADD `from` VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP from_id');
        $this->addSql('ALTER TABLE testimonial DROP FOREIGN KEY FK_E6BDCDF7F675F31B');
        $this->addSql('DROP INDEX UNIQ_E6BDCDF7F675F31B ON testimonial');
        $this->addSql('ALTER TABLE testimonial ADD author INT NOT NULL, DROP author_id');
    }
}
