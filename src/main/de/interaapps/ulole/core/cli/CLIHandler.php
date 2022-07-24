<?php

namespace de\interaapps\ulole\core\cli;

abstract class CLIHandler {
    abstract public function registerCommands(CLI $cli);
}