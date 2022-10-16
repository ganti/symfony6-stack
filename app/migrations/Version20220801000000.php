<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220801000000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'move DB columns';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('ALTER TABLE `user` MODIFY COLUMN `pid` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `id`;');
    $this->addSql('ALTER TABLE `user` MODIFY COLUMN `is_active` TINYINT(1) NOT NULL AFTER `password`;');
    $this->addSql('ALTER TABLE `email` MODIFY COLUMN `pid` VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `id`;');
    $this->addSql('ALTER TABLE `log` MODIFY COLUMN `user_id` INT NULL DEFAULT NULL AFTER `message`;');
    $this->addSql('ALTER TABLE `user_role` MODIFY COLUMN `parent_role_id` INT NULL DEFAULT NULL AFTER `description`;');
  }
}


