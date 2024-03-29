<?php

namespace de\interaapps\ulole\core\cli;

use de\interaapps\ulole\core\cli\modules\create\CreateORMCLI;
use de\interaapps\ulole\core\cli\modules\DBNavigatorCLI;
use de\interaapps\ulole\core\cli\modules\JobCLI;
use de\interaapps\ulole\core\cli\modules\ORMCLI;
use de\interaapps\ulole\core\cli\modules\ReplCLI;
use de\interaapps\ulole\core\WebApplication;

class CLIBootstrapper {
    public CLI $cli;

    public function __construct(
        public array $args = []
    ) {
        $this->cli = new CLI();
    }

    public function addFrameworkHandlers(WebApplication $app): CLIBootstrapper {
        $this->register(new FrameworkCLI())
            ->register(new ReplCLI())
            ->register(new ORMCLI)
            ->register(new DBNavigatorCLI())
            ->register(new CreateORMCLI())
            ->register(new JobCLI($app->getJobHandler()));
        return $this;
    }

    public function register(CLIHandler $cliHandler): CLIBootstrapper {
        $cliHandler->registerCommands($this->cli);
        return $this;
    }

    public function setApplicationAttrib(string $key, mixed $value): CLIBootstrapper {
        $this->cli->setApplicationAttrib($key, $value);
        return $this;
    }

    public function run(): void {
        $command = $this->args[1];
        $this->cli->run($this->args, $command);
    }

}