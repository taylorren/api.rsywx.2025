<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250709075030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_book_book_nl ON book_book');
        $this->addSql('ALTER TABLE book_book ADD CONSTRAINT FK_D278E839741D53CD FOREIGN KEY (place) REFERENCES book_place (id)');
        $this->addSql('ALTER TABLE book_book ADD CONSTRAINT FK_D278E8399CE8D546 FOREIGN KEY (publisher) REFERENCES book_publisher (id)');
        $this->addSql('ALTER TABLE book_book RENAME INDEX bookid_unique TO UNIQ_D278E83936BB5955');
        $this->addSql('ALTER TABLE book_headline ADD CONSTRAINT FK_F91777E44AF2B3F3 FOREIGN KEY (bid) REFERENCES book_book (id)');
        $this->addSql('ALTER TABLE book_headline RENAME INDEX bid_unique TO UNIQ_F91777E44AF2B3F3');
        $this->addSql('ALTER TABLE book_review ADD CONSTRAINT FK_50948A4B47653625 FOREIGN KEY (hid) REFERENCES book_headline (hid)');
        $this->addSql('DROP INDEX bookid ON book_taglist');
        $this->addSql('ALTER TABLE book_taglist ADD CONSTRAINT FK_9B5D53764AF2B3F3 FOREIGN KEY (bid) REFERENCES book_book (id)');
        $this->addSql('DROP INDEX unique_bookid ON book_visit');
        $this->addSql('ALTER TABLE book_visit ADD CONSTRAINT FK_4DBCFC4036BB5955 FOREIGN KEY (bookid) REFERENCES book_book (id)');
        $this->addSql('ALTER TABLE lakers CHANGE selfscore selfscore INT NOT NULL, CHANGE oppscore oppscore INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lakers CHANGE selfscore selfscore INT DEFAULT 0 NOT NULL, CHANGE oppscore oppscore INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE book_headline DROP FOREIGN KEY FK_F91777E44AF2B3F3');
        $this->addSql('ALTER TABLE book_headline RENAME INDEX uniq_f91777e44af2b3f3 TO bid_UNIQUE');
        $this->addSql('ALTER TABLE book_review DROP FOREIGN KEY FK_50948A4B47653625');
        $this->addSql('ALTER TABLE book_taglist DROP FOREIGN KEY FK_9B5D53764AF2B3F3');
        $this->addSql('CREATE UNIQUE INDEX bookid ON book_taglist (bid, tag)');
        $this->addSql('ALTER TABLE book_visit DROP FOREIGN KEY FK_4DBCFC4036BB5955');
        $this->addSql('CREATE UNIQUE INDEX unique_bookid ON book_visit (bookid, visitwhen)');
        $this->addSql('ALTER TABLE book_book DROP FOREIGN KEY FK_D278E839741D53CD');
        $this->addSql('ALTER TABLE book_book DROP FOREIGN KEY FK_D278E8399CE8D546');
        $this->addSql('CREATE INDEX idx_book_book_nl ON book_book (location)');
        $this->addSql('ALTER TABLE book_book RENAME INDEX uniq_d278e83936bb5955 TO bookid_UNIQUE');
    }
}
