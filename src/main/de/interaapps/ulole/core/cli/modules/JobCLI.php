<?php


namespace de\interaapps\ulole\core\cli\modules;

use de\interaapps\ulole\core\cli\CLI;
use de\interaapps\ulole\core\cli\CLIHandler;
use de\interaapps\ulole\core\cli\Colors;
use de\interaapps\ulole\core\jobs\JobHandler;

class JobCLI extends CLIHandler
{

    private $jobHandler;

    public function __construct(JobHandler $jobHandler) {
        $this->jobHandler = $jobHandler;
    }

    public function registerCommands(CLI $cli)
    {

        $cli->register("jobs:work", function ($args) {
            while (true) {
                Colors::info("Executing...");

                foreach ($this->jobHandler->handleAll() as $exception) {
                    Colors::error("Error while execution: ".$exception->getMessage());
                }
                sleep(isset($args[2]) ? $args[2] : 3);
            }
        }, "A script to wait for new jobs and runs them");
    }
}