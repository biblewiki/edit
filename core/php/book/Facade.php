<?php
declare(strict_types = 1);

namespace biwi\edit\book;

use biwi\edit;

/**
 * Class Book
 *
 */
class Facade {
    /**
     * @var ki\App
     */
    protected $app;

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Facade constructor.
     * @param ki\App $app
     */
    public function __construct(edit\App $app) {
        $this->app = $app;
    }

    /**
     * Gibt die Antwort für ein Combo. Hier ist es speziell, da die Kapitel und Verse vom Buch abhängig sind
     * @param \stdClass $args
     * @return ki\Rpc\ResponseCombo
     */
    public function getForCombo(\stdClass $args): edit\Rpc\ResponseDefault {

        // Bücher aus DB laden
        $qryBld = new edit\SqlSelector('book');
        $qryBld->addSelectElement('book.bookId');
        $qryBld->addSelectElement('book.name');
        $qryBld->addSelectElement('book.countChapter');

        $qryBld->addWhereElement('book.state < 100');

        // Nur die letzte Version laden
        $qryBld->addWhereElement('book.version = (SELECT
            MAX(version)
        FROM
            book AS bookVersion
        WHERE book.bookId = bookVersion.bookId)');

        $books = $qryBld->execute($this->app->getDb());
        unset ($qryBld);

        $qryBld = new edit\SqlSelector('bookVers');
        $qryBld->addSelectElement('bookVers.bookId');
        $qryBld->addSelectElement('bookVers.chapter');
        $qryBld->addSelectElement('bookVers.countVers');

        $qryBld->addWhereElement('bookVers.state < 100');

        // Nur die letzte Version laden
        $qryBld->addWhereElement('bookVers.version = (SELECT
            MAX(version)
        FROM
            bookVers AS bookVersVersion
        WHERE bookVers.bookId = bookVersVersion.bookId)');

        $bookVers = $qryBld->execute($this->app->getDb());
        unset ($qryBld);

        $booksData = [];

        foreach($books as $book) {
            $booksData['names'][] = ['caption' => $book['name'], 'value' => $book['bookId']];
            $booksData['chapters'][$book['bookId']] = [];
            $booksData['verses'][$book['bookId']] = [];

            for ($i = 1; $i <= $book['countChapter']; $i++) {
                $booksData['chapters'][$book['bookId']][] = ['value' => $i];

                foreach($bookVers as $verse) {

                    if ($verse['bookId'] === $book['bookId'] && $verse['chapter'] === $i) {
                        for ($y = 1; $y <= $verse['countVers']; $y++) {
                            $booksData['verses'][$book['bookId']][$i][] = ['value' => $y];
                        }
                    }
                }
            }
        }

        $result = new edit\Rpc\ResponseDefault();
        $result->books = $booksData;
        return $result;
    }


    /**
     * Gibt die Kapitel für ein Buch zurück
     *
     * @param string $book
     * @return array
     */
    public function getChaptersForBook(int $book): array {

        // TODO: aus DB holen

        $chapters = [];

        for ($i = 1; $i<=12; $i++){
            $chapters[] = ['value' => $i];
        }


        foreach ($chapters as $chapter) {
            $verses[$chapter['value']] = self::getVersesForChapter($book, $chapter['value']);
        }

        return ['chapters' => $chapters, 'verses' => $verses];
    }


    /**
     * Gibt die Verse für ein Buch und Kapitel zurück
     *
     * @param string $book
     * @return array
     */
    public function getVersesForChapter(int $book, int $chapter): array {

        // TODO: aus DB holen

        $verses = [];

        for ($i = 1; $i<=30; $i++){
            $verses[] = ['value' => $i];
        }
        return $verses;
    }
}
