<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015165636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_categories (blog_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_DC356481DAE07E97 (blog_id), INDEX IDX_DC35648112469DE2 (category_id), PRIMARY KEY(blog_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blog_categories ADD CONSTRAINT FK_DC356481DAE07E97 FOREIGN KEY (blog_id) REFERENCES blog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_categories ADD CONSTRAINT FK_DC35648112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog DROP category');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_categories DROP FOREIGN KEY FK_DC356481DAE07E97');
        $this->addSql('ALTER TABLE blog_categories DROP FOREIGN KEY FK_DC35648112469DE2');
        $this->addSql('DROP TABLE blog_categories');
        $this->addSql('DROP TABLE category');
        $this->addSql('ALTER TABLE blog ADD category VARCHAR(255) DEFAULT NULL');
    }
}
