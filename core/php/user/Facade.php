<?php
declare(strict_types = 1);

namespace biwi\edit\user;

use biwi\edit;

/**
 * Class Facade
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
     * @param edit\App $app
     */
    public function __construct(edit\App $app) {
        $this->app = $app;
    }


    /**
     * Löschen
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseDefault
     * @throws \Throwable
     * @throws edit\ExceptionNotice
     * @throws edit\Rpc\Warning
     */
    public function deleteUser(\stdClass $args): edit\Rpc\ResponseDefault {
        try {
            $ids = \property_exists($args, 'selection') ? $args->selection : [];

            // Rechte überprüfen
            if ($this->app->getLoggedInUserRole() < 50) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            if (!$ids) {
                throw new edit\ExceptionNotice($this->app->getText('Es wurde kein Datensatz ausgewählt.'));
            }

            if (!$this->app->isIgnoreWarnings()) {
                throw new edit\Rpc\Warning($this->app->getText('Möchten Sie die ausgewählten Datensätze wirklich löschen?'), $this->app->getText('Löschen') . '?');
            }

            // Transaktion starten
            $this->app->getDb()->beginTransaction();

            // sql
            $st = $this->app->getDb()->prepare('DELETE FROM userRelationship WHERE userRelationshipId = :userRelationshipId');

            foreach ($ids as $id) {
                $st->bindValue(':userRelationshipId', $id, \PDO::PARAM_INT);
                $st->execute();
            }
            unset ($st);

            // Kategorie holen
            $category = edit\app\App::getCategoryByName($this->app, 'user');

            // Quellen aus DB löschen
            $deleteSources = new edit\DeleteSource($this->app, $category, 'userRelationship');
            $deleteSources->delete($ids);

            // Transaktion beenden
            $this->app->getDb()->commit();

            $response = new edit\Rpc\ResponseDefault();
            $response->return = $ids;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }


    /**
     * Grid Data zurückgeben
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseGrid
     * @throws edit\ExceptionNotice
     */
    public function getGridData(\stdClass $args): edit\Rpc\ResponseGrid {

        // Rechte überprüfen
        if ($this->app->getLoggedInUserRole() < 50) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $loader = new edit\GridLoader($this->app, $args, 'user');

        // Status Benutzer
        $userStatus = '
            IF(user.state = 10, :status_10,
                IF(user.state = 20, :status_20,
                    IF(user.state = 30, :status_30,
                        IF(user.state = 40, :status_40,
                            IF(user.state = 50, :status_50,
                                IF(user.state = 60, :status_60,
                                    IF(user.state = 70, :status_70,
                                        IF(user.state = 80, :status_80, :status_ukn)
                                    )
                                )
                            )
                        )
                    )
                )
            )';

        $loader->getQueryBuilderForSelect()->addParam(':status_10', $this->app->getText('Email noch nicht bestätigt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_10', $this->app->getText('Email noch nicht bestätigt'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_20', $this->app->getText('Email bestätigt, noch nicht eingeloggt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_20', $this->app->getText('Email bestätigt, noch nicht eingeloggtf'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_30', $this->app->getText(' Erstes Mal eingeloggt,'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_30', $this->app->getText(' Erstes Mal eingeloggt,'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_40', $this->app->getText('Normal'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_40', $this->app->getText('Normal'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_50', $this->app->getText('Passwort Token versendet,'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_50', $this->app->getText('Passwort Token versendet,'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_60', $this->app->getText('Passwort zurückgesetzt und noch nicht eingeloggt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_60', $this->app->getText('Passwort zurückgesetzt und noch nicht eingeloggt'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_70', $this->app->getText('Google registriert und noch nicht eingeloggt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_70', $this->app->getText('Google registriert und noch nicht eingeloggt'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_80', $this->app->getText('Telegram registriert und noch nicht eingeloggt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_80', $this->app->getText('Telegram registriert und noch nicht eingeloggt'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_ukn', $this->app->getText('Unbekannt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_ukn', $this->app->getText('Unbekannt'), \PDO::PARAM_STR);

        // Status Email
        $emailStatus = '
            IF(user.emailState = 10, :emailStatus_10,
                IF(user.emailState = 20, :emailStatus_20,
                    IF(user.emailState = 30, :emailStatus_30,
                        IF(user.emailState = 40, :emailStatus_40, :emailStatus_ukn)
                    )
                )
            )';

        $loader->getQueryBuilderForSelect()->addParam(':emailStatus_10', $this->app->getText('Unbestätigt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':emailStatus_10', $this->app->getText('Unbestätigt'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':emailStatus_20', $this->app->getText('Bestätigungsmail versendet'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':emailStatus_20', $this->app->getText('Bestätigungsmail versendet'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':emailStatus_30', $this->app->getText('Mehrere Bestätigungsmails versendet'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':emailStatus_30', $this->app->getText('Mehrere Bestätigungsmails versendet'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':emailStatus_40', $this->app->getText('Bestätigt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':emailStatus_40', $this->app->getText('Bestätigt'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':emailStatus_ukn', $this->app->getText('Unbekannt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':emailStatus_ukn', $this->app->getText('Unbekannt'), \PDO::PARAM_STR);

        // Status Passwort
        $passwortStatus = '
            IF(user.passwordState = 10, :passwordStatus_10,
                IF(user.passwordState = 20, :passwordStatus_20,
                    IF(user.passwordState = 30, :passwordStatus_30,
                        IF(user.passwordState = 40, :passwordStatus_40,
                            IF(user.passwordState = 50, :passwordStatus_50,
                                IF(user.passwordState = 60, :passwordStatus_60, :passwordStatus_ukn)
                            )
                        )
                    )
                )
            )';

        $loader->getQueryBuilderForSelect()->addParam(':passwordStatus_10', $this->app->getText('Normal'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':passwordStatus_10', $this->app->getText('Normal'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':passwordStatus_20', $this->app->getText('Zurücksetzen angefordert'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':passwordStatus_20', $this->app->getText('Zurücksetzen angefordert'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':passwordStatus_30', $this->app->getText('Rücksetzen-Email versendet'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':passwordStatus_30', $this->app->getText('Rücksetzen-Email versendet'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':passwordStatus_40', $this->app->getText('Zurücksetzen fehlgeschlagen'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':passwordStatus_40', $this->app->getText('Zurücksetzen fehlgeschlagen'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':passwordStatus_50', $this->app->getText('Zurückgesetzt und noch nicht eingeloggt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':passwordStatus_50', $this->app->getText('Zurückgesetzt und noch nicht eingeloggt'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':passwordStatus_60', $this->app->getText('Zurücksetzen gesperrt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':passwordStatus_60', $this->app->getText('Zurücksetzen gesperrt'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':passwordStatus_ukn', $this->app->getText('Unbekannt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':passwordStatus_ukn', $this->app->getText('Unbekannt'), \PDO::PARAM_STR);

        // Primary Keys
        $loader->addPrimaryColumn('user.userId', $this->app->getText('Benutzer') . ' ' . $this->app->getText('ID'));

        $loader->addColumn($this->app->getText('Benutzername'), 'user.username');
        $loader->addColumn($this->app->getText('Vorname'), 'user.firstName');
        $loader->addColumn($this->app->getText('Nachname'), 'user.lastName');
        $loader->addColumn($this->app->getText('Email'), 'user.email');
        $loader->addColumn($this->app->getText('Email Status'), $emailStatus);
        $loader->addColumn($this->app->getText('Passwort Status'), $passwortStatus, ['width' => 110]);
        $loader->addColumn($this->app->getText('Profilbild'), 'user.profilePicture');
        $loader->addColumn($this->app->getText('Telegram Id'), 'user.telegramId');
        $loader->addColumn($this->app->getText('Google Id'), 'user.googleId');
        $loader->addColumn($this->app->getText('Rolle'), 'role.name');
        $loader->addColumn($this->app->getText('Letztes Login'), 'user.lastLogin', ['width' => 110, 'xtype' => 'kijs.gui.grid.columnConfig.Date', 'format' => 'd.m.Y H:i']);
        $loader->addColumn($this->app->getText('Status'), $userStatus);

        $loader->addFromElement('INNER JOIN role ON role.roleId = user.roleId');

        $loader->addWhereElement('userID != 1');

        return $loader->load();
    }


    /**
     * Gibt das Formular zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseForm
     */
    public function getFormData(\stdClass $args): edit\Rpc\ResponseForm {
        $userId = null;

        // Rechte überprüfen
        if ($this->app->getLoggedInUserRole() < 50) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'selection') && $args->selection && property_exists($args->selection, 'userId') && $args->selection->userId) {
            $userId = $args->selection->userId;
        }

        $row = [];
        if ($userId) {
            $qryBld = new edit\SqlSelector('user');
            $qryBld->addSelectElement('user.userId');
            $qryBld->addSelectElement('user.username');
            $qryBld->addSelectElement('user.firstName');
            $qryBld->addSelectElement('user.lastName');
            $qryBld->addSelectElement('user.email');
            $qryBld->addSelectElement('user.passwordState');
            $qryBld->addSelectElement('user.languageId');
            $qryBld->addSelectElement('user.telegramId');
            $qryBld->addSelectElement('user.googleId');
            $qryBld->addSelectElement('user.roleId');
            $qryBld->addSelectElement('user.state');

            $qryBld->addWhereElement('user.userId = :userId');
            $qryBld->addParam(':userId', $userId, \PDO::PARAM_INT);

            $row = $qryBld->execute($this->app->getDb(), false);
            unset ($qryBld);

        } else {
            $row['userId'] = null;
            $row['username'] = null;
            $row['firstName'] = null;
            $row['lastName'] = null;
            $row['email'] = null;
            $row['passwordState'] = null;
            $row['languageId'] = null;
            $row['telegramId'] = null;
            $row['googleId'] = null;
            $row['roleId'] = null;
            $row['state'] = null;
        }

        // neuer Datensatz?
        if (\property_exists($args, 'create') && $args->create === true) {
            unset($row['userId']);
        }

        $row['openTS'] = date('Y-m-d H:i:s');

        $return = new edit\Rpc\ResponseForm();
        $return->setFormData($row);
        return $return;
    }


    /**
     * Personen für Combo zurückgeben
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getForCombo(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $users = edit\user\Person::getPersons($this->app, $args);

        // Name mit Beschreibung für Combo erstellen
        foreach ($users as &$user) {
            $user['comboName'] = $user['name'] . ':  ' .$user['description'];
        }

        $response = new edit\Rpc\ResponseCombo();
        $response->addRows($users);
        return $response;
    }


    /**
     * Speichert das Formular
     *
     * @param \stdClass $args
     * @return edit\Rpc\ResponseDefault
     */
    public function saveDetailForm(\stdClass $args): edit\Rpc\ResponseDefault {
        try {
            $tableName = 'user';
            $formPacket = (array)$args->formData;

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserRole()) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $category = edit\app\App::getCategoryByName($this->app, $tableName);

            $formPacket['categoryId'] = $category['categoryId'];

            if ($formPacket['userId']) {
                $formPacket['id'] = $formPacket['userId'];
                $formPacket['oldVal_userId'] = $formPacket['userId'];
            }

            if ($formPacket['version']) {
                $formPacket['oldVal_version'] = $formPacket['version'];
            }

            $save = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), $tableName);
            $save->save($formPacket);
            $userId = (int)$save->getPrimaryKey()->value;
            $version = (int)$save->getVersion();
            unset ($save);

            $formPacket['userId'] = $userId;
            $formPacket['version'] = $version;

            if ($formPacket['proficiency']) {
                $saveProfeciency = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), 'userProficiency');
                $saveProfeciency->save($formPacket);
                unset ($saveProfeciency);
            }

            // Beziehungen speichern wenn vorhaden
            if ($formPacket['relationships']) {
                $saveRelationship = edit\user\Person::saveRelationship($this->app, $formPacket);
            }

            // Gruppen speichern wenn vorhaden
            if ($formPacket['groups']) {
                $saveGroups = edit\user\Person::saveGroup($this->app, $formPacket);
            }

            // Namen speichern
            if ($formPacket['names']) {
                $saveNames = edit\user\Person::saveNames($this->app, $formPacket);
            }

            // Quellen speichern wenn vorhaden
            if ($formPacket['sources']) {
                $saveSource = new edit\SaveSource($this->app, $category);
                $saveSource->save($formPacket);
                unset($saveSource);
            }

            $response = new edit\Rpc\ResponseDefault();
            $response->id = $userId;
            $response->version = $version;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }
}
