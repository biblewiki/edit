<?php
declare(strict_types = 1);

namespace biwi\edit\bible;

use biwi\edit;

/**
 * Class Bible
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
     * Gibt die Antwort für ein Combo
     * @param \stdClass $args
     * @return ki\Rpc\ResponseCombo
     */
    public function getBibleBooks(\stdClass $args): edit\Rpc\ResponseDefault {
//        $comboLoader = new edit\ComboLoader($this->app, $args, 'kapitel');
//        $comboLoader->setCaptionSql('CONCAT(kapitel.kapitelNr, \' - \', kapitel_text.bezeichnung)');
//        $comboLoader->setValueSql('kapitel.kapitelNr', true);
//
//        $comboLoader->getQueryBuilder()->addFromElement('INNER JOIN kapitel_text ON kapitel.kapitelNr = kapitel_text.kapitelNr AND kapitel_text.languageId = :languageId');
//        $comboLoader->getQueryBuilder()->addParam(':languageId', $this->app->getLanguageId(), \PDO::PARAM_STR);
//
//        if ($brancheId) {
//            $comboLoader->getQueryBuilder()->addWhereElement('kapitel.brancheId = :brancheId');
//            $comboLoader->getQueryBuilder()->addParam(':brancheId', $brancheId, \PDO::PARAM_INT);
//        }
//
//        return $comboLoader->execute();

        $books = ['names' => [0 => ['caption' => 'TestBuch', 'value' => 1], 1 => ['caption' => 'TestBuch2', 'value' => 2]]];
        $books['verses'] = [];

        foreach ($books['names'] as $book) {
            $result = self::getChaptersForBook($book['value']);
            $books['chapters'][$book['value']] = $result['chapters'];
            $books['verses'][$book['value']] = $result['verses'];
        }

        $rows = new edit\Rpc\ResponseDefault();
        $rows->books = $books;
        return $rows;
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
