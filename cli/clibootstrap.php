<?php
use modules\ulole\cli\CLI;
use modules\ulole\cli\modules\Compile;


$HEADER = "\033[95m";
$OKBLUE = "\033[94m";
$OKGREEN = "\033[92m";
$WARNING = "\033[93m";
$FAIL = "\033[91m";
$ENDC = "\033[0m";
$BOLD = "\033[1m";
$UNDERLINE = "\033[4m";
$RED = "\033[31m";
$BLUE = "\033[34m";
$YELLOW = "\033[33m";
$TURQUIOUS = "\033[36m";
$GREEN = "\033[32m";
$BLINK = "\033[5m";
$BG_RED = "\033[41m";
$BG_BLUE = "\033[44m";
$BG_GREEN = "\033[42m";
$BG_YELLOW = "\033[43m";
$BG_BLACK = "\033[40m";

$OKTEXT = $GREEN."᮰ Done".$ENDC.": ";
$done_prefix = "\033[92m᮰ Done\033[0m: ";
$warn_prefix = "\033[93m᮰ WARNING\033[0m: ";
$error_prefix = "\033[91m᮰ ERROR\033[0m: ";



$cli = new CLI();

$cli->register("repl", function($args) {
    
    require "modules/repl.php";

    return "\n";
});

$cli->register("server", function($args) use ($done_prefix, $warn_prefix) {
    echo "\n-- ULOLE-TESTING SERVER! --\n\n".$done_prefix."listening to \033[34mlocalhost:8000\033[0m\n";

    if (!file_exists("env.json")) 
        echo $warn_prefix." env.json not found!\n";

    if (!file_exists("conf.json")) 
        echo $warn_prefix." conf.json not found!\n";

    $exec= 'cd public
    php -S 0.0.0.0:8000 -t ./ index.php';
    system($exec);
    exec($exec);
    shell_exec($exec);
    
    // This will actually just happens if the system() function crashes
    echo $error_prefix."The server couldn't start!
    Type this to run the server otherwise:
    cd public
    php -S 0.0.0.0:8000 -t ./ testserver.php
    ";
});

$cli->register("compile", function($args) use ($done_prefix, $warn_prefix, $OKTEXT) {
    Compile::compileDir('resources/compile/css/compile.json', "resources/compile/css");
    echo "\n".$OKTEXT."Bundled all files from config file: resources/compile/css/compile.json";
    Compile::compileDir('resources/compile/js/compile.json', "resources/compile/js");
    echo "\n".$OKTEXT."Bundled all files from config file: resources/compile/js/compile.json";
    Compile::compileViews("resources/compile/views/", "resources/views/");
    echo "\n".$OKTEXT."Compiled all view.php files in directory resources/compile/views/\n";
});

foreach (scandir("modules") as $folder){
    if (is_dir($folder) && $folder != "." && $folder != "..") {
        if (file_exists("modules/".$folder."/cli.php")) {
            require_once "modules/".$folder."/cli.php";
        }
    }
}

$cli->run($argv[1], $argv);