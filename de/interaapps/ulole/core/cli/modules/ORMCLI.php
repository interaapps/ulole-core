<?php
namespace de\interaapps\ulole\core\cli\modules;

use de\interaapps\ulole\core\cli\CLI;
use de\interaapps\ulole\core\cli\CLIHandler;
use de\interaapps\ulole\orm\migration\Migrator;

class ORMCLI extends CLIHandler {
    public function registerCommands(CLI $cli) {
        $cli->register("migrate:up", function(){
            (new Migrator("main"))
                ->setLogging(true)
                ->fromFolder("resources/migrations")
                ->up();
        }, "Migrates a database");

        $cli->register("migrate:down", function($args){
            if (!isset($args[2]))
                $args[2] = 1;
            (new Migrator("main"))
                ->setLogging(true)
                ->fromFolder("resources/migrations")
                ->down();
        }, "Downgrades a database");
    }
}