<?php

namespace de\interaapps\ulole\core\cli\modules;

use de\interaapps\ulole\core\cli\CLI;
use de\interaapps\ulole\core\cli\CLIHandler;
use de\interaapps\ulole\core\cli\Colors;
use de\interaapps\ulole\orm\migration\Migrator;
use de\interaapps\ulole\orm\migration\table\MigrationModel;
use de\interaapps\ulole\orm\UloleORM;

class ORMCLI extends CLIHandler {
    public function registerCommands(CLI $cli) {
        $cli->register("migrate:up", function ($args) {
            if (!isset($args[2]))
                $args[2] = "main";
            (new Migrator($args[2]))
                ->setLogging(true)
                ->fromFolder("resources/migrations")
                ->up();
        }, "Migrates a database");

        $cli->register("migrate:auto", function () {
            UloleORM::autoMigrate();
        }, "Calls UloleORM::autoMigrate()");

        $cli->register("migrate:down", function ($args) {
            if (!isset($args[2]))
                $args[2] = 1;

            if (!isset($args[3]))
                $args[3] = "main";

            (new Migrator($args[3]))
                ->setLogging(true)
                ->fromFolder("resources/migrations")
                ->down($args[2]);
        }, "Downgrades a database");
        $cli->register("migrate:status", function () {
            if (!isset($args[2]))
                $args[2] = "main";
            $migrations = (new Migrator($args[2]))
                ->setLogging(true)
                ->fromFolder("resources/migrations")
                ->getMigrations();

            $modelLength = 20;
            foreach ($migrations as $migrationClazz) {
                $migrationClazz = str_replace("resources\\migrations", "", $migrationClazz);
                if (strlen($migrationClazz) > $modelLength)
                    $modelLength = strlen($migrationClazz);
            }
            echo str_pad(" model", $modelLength) . " | migrated | version\n";

            foreach ($migrations as $migrationClazz) {
                $migrationModel = MigrationModel::table($args[2])->where("migrated_model", $migrationClazz)->first();
                $exists = $migrationModel !== null;

                $migrationClazzSplitted = explode("\\", $migrationClazz);

                echo str_pad(" " . $migrationClazzSplitted[count($migrationClazzSplitted) - 1], $modelLength)
                    . " | " . ($exists ? Colors::GREEN : Colors::RED) . str_pad(($exists ? "YES" : "NO"), 8) . Colors::ENDC
                    . " | " . ($exists ? Colors::TURQUIOUS . $migrationModel->version : Colors::GRAY . "--") . Colors::ENDC . "\n";
            }
        }, "Get the status of the migration");
    }
}