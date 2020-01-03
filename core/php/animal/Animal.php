<?php
declare(strict_types = 1);

namespace biwi\edit\animal;

use biwi\edit;

/**
 * Class Animal
 */
class Animal {

    /**
     * Gibt die Tiere zurück
     *
     * @param \biwi\edit\App $app
     * @param \stdClass $args
     * @return array
     */
    public static function getAnimals(edit\App $app, \stdClass $args): array {

        $animalId = null;
        $onlyOthers = null;

        // Überprüfen ob einen ID übergeben wurde
        if (property_exists($args, 'animalId') && $args->animalId) {
            $animalId = $args->animalId;
        }

        // Überprüfen ob ein Tier nicht übergeben werden darf
        if (property_exists($args, 'onlyOthers') && $args->onlyOthers) {
            $onlyOthers = $args->onlyOthers;
        }

        // SQL
        $qryBld = new edit\SqlSelector('animal');
        $qryBld->addSelectElement('animal.animalId AS secondAnimalId');
        $qryBld->addSelectElement('animal.animalSpecies');

        if ($animalId) {

            // Nur alle anderen Tiere laden
            if ($onlyOthers) {
                $qryBld->addWhereElement('animal.animalId != :animalId');
                $qryBld->addParam(':animalId', $animalId, \PDO::PARAM_INT);

            // Gibt das Tier zurück
            } else {
                $qryBld->addWhereElement('animal.animalId = :animalId');
                $qryBld->addParam(':animalId', $animalId, \PDO::PARAM_INT);
            }
        }

        // Nur die erste Version laden
        $qryBld->addWhereElement('animal.version = (SELECT
                MIN(version)
            FROM
                animal AS animalVersion
            WHERE animal.animalId = animalVersion.animalId)');

        $rows = $qryBld->execute($app->getDb());
        unset ($qryBld);

        return $rows;
    }
   

}