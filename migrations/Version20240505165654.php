<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240505165654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'hw2 пересоздал все таблицы';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "group_user" (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, group_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX group_user__group_id__idx ON "group_user" (group_id)');
        $this->addSql('CREATE INDEX group_user__user_id__idx ON "group_user" (user_id)');
        $this->addSql('CREATE UNIQUE INDEX group_user__group_id_user_id__uq ON "group_user" (group_id, user_id)');
        $this->addSql('CREATE TABLE "groups" (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, name VARCHAR(120) NOT NULL, limit_teachers INT NOT NULL, limit_students INT NOT NULL, level SMALLINT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, skill_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX groups__name__idx ON "groups" (name)');
        $this->addSql('CREATE INDEX groups__skill__idx ON "groups" (skill_id)');
        $this->addSql('CREATE TABLE "role" (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, name VARCHAR(120) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX roles__name__idx ON "role" (name)');
        $this->addSql('CREATE UNIQUE INDEX roles__name__uq ON "role" (name)');
        $this->addSql('CREATE TABLE "skills" (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, name VARCHAR(120) NOT NULL, level SMALLINT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX skills__name_level__uq ON "skills" (name, level)');
        $this->addSql('COMMENT ON TABLE "skills" IS \'Навык и уровень владения\'');
        $this->addSql('CREATE TABLE "user_role" (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, user_id BIGINT DEFAULT NULL, role_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX user_role__user_id__idx ON "user_role" (user_id)');
        $this->addSql('CREATE INDEX user_role__role_id__idx ON "user_role" (role_id)');
        $this->addSql('CREATE UNIQUE INDEX user_role__user_id_role_id__uq ON "user_role" (user_id, role_id)');
        $this->addSql('CREATE TABLE "user_skill" (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, user_id BIGINT DEFAULT NULL, skill_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX user_skill__user_id__idx ON "user_skill" (user_id)');
        $this->addSql('CREATE INDEX user_skill__skill_id__idx ON "user_skill" (skill_id)');
        $this->addSql('CREATE UNIQUE INDEX user_skill__skill_id_user_id__uq ON "user_skill" (user_id, skill_id)');
        $this->addSql('CREATE TABLE "users" (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, email VARCHAR(150) NOT NULL, name VARCHAR(120) NOT NULL, surname VARCHAR(120) NOT NULL, status SMALLINT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX users__email__uq ON "users" (email)');
        $this->addSql('ALTER TABLE "group_user" ADD CONSTRAINT group_user__group_id_fk FOREIGN KEY (group_id) REFERENCES "groups" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "group_user" ADD CONSTRAINT group_user__user_id_fk FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "groups" ADD CONSTRAINT groups__skill_id_fk FOREIGN KEY (skill_id) REFERENCES "skills" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user_role" ADD CONSTRAINT user_role__user_id_fk FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user_role" ADD CONSTRAINT user_role__role_id_fk FOREIGN KEY (role_id) REFERENCES "role" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user_skill" ADD CONSTRAINT user_skill__user_id_fk FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user_skill" ADD CONSTRAINT user_skill__skill_id_fk FOREIGN KEY (skill_id) REFERENCES "skills" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "group_user" DROP CONSTRAINT group_user__user_id_fk');
        $this->addSql('ALTER TABLE "group_user" DROP CONSTRAINT group_user__group_id_fk');
        $this->addSql('ALTER TABLE "groups" DROP CONSTRAINT groups__skill_id_fk');
        $this->addSql('ALTER TABLE "user_role" DROP CONSTRAINT user_role__role_id_fk');
        $this->addSql('ALTER TABLE "user_role" DROP CONSTRAINT user_role__user_id_fk');
        $this->addSql('ALTER TABLE "user_skill" DROP CONSTRAINT user_skill__skill_id_fk');
        $this->addSql('ALTER TABLE "user_skill" DROP CONSTRAINT user_skill__user_id_fk');
        $this->addSql('DROP TABLE "group_user"');
        $this->addSql('DROP TABLE "groups"');
        $this->addSql('DROP TABLE "role"');
        $this->addSql('DROP TABLE "skills"');
        $this->addSql('DROP TABLE "user_role"');
        $this->addSql('DROP TABLE "user_skill"');
        $this->addSql('DROP TABLE "users"');
    }
}
