<?php
namespace de\interaapps\ulole\core\cli;

use de\interaapps\ulole\core\cli\modules\create\CreateORMCLI;
use de\interaapps\ulole\core\cli\modules\ReplCLI;

class CLIBootstrapper {
    public $cli, $args;

    public function __construct($args)
    {
        $this->cli = new CLI();
        $this->args = $args;
        $this->register(new FrameworkCLI())
            ->register(new ReplCLI())
            ->register(new CreateORMCLI())
            ;
    }
    public function register(CLIHandler $cliHandler) : CLIBootstrapper
    {
        $cliHandler->registerCommands($this->cli);
        return $this;
    }

    public function run()
    {
        $command = $this->args[1];
        $this->cli->run($this->args, $command);
    }
}