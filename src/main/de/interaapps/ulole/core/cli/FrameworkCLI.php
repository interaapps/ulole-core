<?php

namespace de\interaapps\ulole\core\cli;

class FrameworkCLI extends CLIHandler {
    public function registerCommands(CLI $cli) {
        $cli->register("serve", function () {
            $exec = 'cd public && php -S 0.0.0.0:8000 -t ./ index.php';
            system($exec);
            exec($exec);
            shell_exec($exec);
        }, "A testing server 'cd public && php -S 0.0.0.0:8000 -t ./ index.php'");
    }
}