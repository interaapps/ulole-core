<?php
namespace de\interaapps\ulole\core;

use de\interaapps\ulole\core\config\Configuration;
use de\interaapps\ulole\orm\Database;
use de\interaapps\ulole\orm\UloleORM;
use de\interaapps\ulole\router\Router;

abstract class WebApplication {
    private $router;
    private $environment;
    private $inCLI = false;

    public function start(Environment $environment){
        $this->environment = $environment;

        $this->router = (new Router)
            ->setIncludeDirectory("resources/views")
            ->setNamespace("app\\controller");

        $this->init();
        
        /**
         * If in web and not in cli
         */
        if (!$this->inCLI) {
            $this->run();
            $this->router->run();
        }

        return $this;
    }

    public abstract function init();
    public abstract function run();

    public function getRouter() {
        return $this->router;
    }

    public function initDatabase($configEntry, $name = null) : WebApplication {
        if ($name === null) {
            $name = "main";
            $entry = $this->getConfig()->get($configEntry.".name", null);
            if ($entry !== null) {
                $name = $entry;
            }
        }
        

        UloleORM::database($name, new Database(
            $this->getConfig()->get($configEntry.".username"),
            $this->getConfig()->get($configEntry.".password"),
            $this->getConfig()->get($configEntry.".database"),
            $this->getConfig()->get($configEntry.".server", 'localhost'),
            $this->getConfig()->get($configEntry.".port", 3306),
            $this->getConfig()->get($configEntry.".driver", "mysql")
        ));

        return $this;
    }

    public function getEnvironment() : Environment {
        return $this->environment;
    }

    public function getConfig() : Configuration {
        return $this->environment->getConfig();
    }

    public function setInCLI($inCLI) : WebApplication {
        $this->inCLI = $inCLI;
        return $this;
    }
}