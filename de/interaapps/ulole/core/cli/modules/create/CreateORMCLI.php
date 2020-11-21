<?php
namespace de\interaapps\ulole\core\cli\modules\create;

use de\interaapps\ulole\core\cli\CLI;
use de\interaapps\ulole\core\cli\CLIHandler;
use de\interaapps\ulole\core\cli\Colors;

class CreateORMCLI extends CLIHandler {
    public function registerCommands(CLI $cli) {
        $cli->register("create:model", function($args){
            if (isset($args[2])) {
                $outputFile = "app/model/".$args[2].".php";
                if (file_exists($outputFile)) {
                    echo "The model already exists. Do you want to override it? [type y] \n";
                    if (readline("Continue (y): ") != 'y')
                        return "Declined";
                }
                $output = "<?php
namespace app\model;

use de\interaapps\ulole\orm\ORMModel;

class $args[2] {
    use ORMModel;
    
    public \$id;

    protected \$ormSettings = [
        'identifier' => 'id'
    ];
}";
                Colors::done("Created model in $outputFile!");
                $lower = $args[2].'s';
                $lower[0] = strtolower($lower[0]);
                echo
                    "\nTo register: \n\n"
                    .Colors::BG_BLACK
                    ."  "
                    .Colors::YELLOW
                        ."UloleORM".Colors::TURQUIOUS."::"
                        .Colors::BLUE."register".
                            Colors::TURQUIOUS."(".
                                Colors::GREEN.'"'.$lower.'"'.
                                Colors::TURQUIOUS.", ".
                                Colors::YELLOW."\\app\\model\\".$args[2].Colors::TURQUIOUS."::".Colors::BLUE."class".
                            Colors::TURQUIOUS.");".
                    "  ".
                    Colors::ENDC."\n\n";
                file_put_contents($outputFile, $output);
            } else {
                Colors::error("Please give me a name!");
            }
        }, "Create a model");
    }
}