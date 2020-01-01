<?php
declare(strict_types = 1);

namespace biwi\edit\app;

use biwi\edit;

/**
 * Class App
 */
class App {

    /**
     * Gibt die Kategorie anhand des Namens zurück
     *
     * @param \biwi\edit\App $app
     * @param type $categoryName
     * @return array
     */
    public function getCategoryByName(edit\App $app, $categoryName): array {

        $qryBld = new edit\SqlSelector('category');
        $qryBld->addSelectElement('category.categoryId');
        $qryBld->addSelectElement('category.categoryGroupId');
        $qryBld->addSelectElement('category.name');
        $qryBld->addSelectElement('category.tableName');

        $qryBld->addWhereElement('category.tableName = :categoryName OR category.name = :categoryName');
        $qryBld->addParam(':categoryName', $categoryName, \PDO::PARAM_STR);

        $category = $qryBld->execute($app->getDb(), false);
        unset ($qryBld);

        return $category;
    }


    /**
     * Gibt die Kategorie anhand des Namens zurück
     *
     * @param \biwi\edit\App $app
     * @param type $categoryId
     * @return array
     */
    public function getCategoryById(edit\App $app, $categoryId): array {

        $qryBld = new edit\SqlSelector('category');
        $qryBld->addSelectElement('category.categoryId');
        $qryBld->addSelectElement('category.categoryGroupId');
        $qryBld->addSelectElement('category.name');
        $qryBld->addSelectElement('category.tableName');

        $qryBld->addWhereElement('category.categoryId = :categoryId');
        $qryBld->addParam(':categoryId', $categoryId, \PDO::PARAM_INT);

        $category = $qryBld->execute($app->getDb(), false);
        unset ($qryBld);

        return $category;
    }
}
