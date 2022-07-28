<?php

namespace de\interaapps\ulole\core;

use de\interaapps\ulole\core\cli\CLIBootstrapper;
use de\interaapps\ulole\core\config\Configuration;
use de\interaapps\ulole\core\jobs\JobHandler;
use de\interaapps\ulole\orm\Database;
use de\interaapps\ulole\orm\UloleORM;
use de\interaapps\ulole\router\Router;

abstract class WebApplication {
    private Router $router;
    private Environment $environment;
    private bool $inCLI = false;
    private JobHandler $jobHandler;

    public function start(Environment $environment) : WebApplication {
        $this->environment = $environment;

        $this->router = (new Router())
            ->setIncludeDirectory("resources/views")
            ->jsonResponseTransformer();

        $this->jobHandler = new JobHandler();

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

    public abstract function init(): void;

    public abstract function run(): void;

    public function initCLI(CLIBootstrapper $cli): void {}

    public function getRouter(): Router {
        return $this->router;
    }

    public function initDatabase($configEntry, $name = null): WebApplication {
        if ($name === null) {
            $name = "main";
            $entry = $this->getConfig()->get($configEntry . ".name", null);
            if ($entry !== null) {
                $name = $entry;
            }
        }


        UloleORM::database($name, new Database(
            username: $this->getConfig()->get($configEntry . ".username"),
            password: $this->getConfig()->get($configEntry . ".password"),
            database: $this->getConfig()->get($configEntry . ".database"),
            host:     $this->getConfig()->get($configEntry . ".server", 'localhost'),
            port:     $this->getConfig()->get($configEntry . ".port", 3306),
            driver:   $this->getConfig()->get($configEntry . ".driver", "mysql")
        ));

        return $this;
    }

    public function getEnvironment(): Environment {
        return $this->environment;
    }

    public function getConfig(): Configuration {
        return $this->environment->getConfig();
    }

    public function setInCLI($inCLI): WebApplication {
        $this->inCLI = $inCLI;
        return $this;
    }

    public function getJobHandler(): JobHandler {
        return $this->jobHandler;
    }

    public function setJobHandler($jobHandler): void {
        $this->jobHandler = $jobHandler;
    }
}