<?php
namespace de\interaapps\ulole\core\cli\modules;

use Exception;
use de\interaapps\ulole\core\cli\CLI;
use de\interaapps\ulole\core\cli\Colors;
use de\interaapps\ulole\core\cli\CLIHandler;

class ReplCLI extends CLIHandler {
    public function registerCommands(CLI $cli) {
        $cli->register("repl", function(){
            ReplCLI::replLoop();
        }, "A simple repl (CLI based testing environment)");
    }

    public static function replLoop(){
        $opened = false;
        $command = "";

        while(true) {       
            register_shutdown_function("de\interaapps\ulole\core\cli\modules\ReplCLI::handleFatal");
            set_error_handler("de\interaapps\ulole\core\cli\modules\ReplCLI::handleError");

            if ($opened !== false) {
                $command .= "\n".readline("... ");
            } else {
                $command = readline(">>> ");
            }
            $lastChar = substr(trim($command), -1);
            if ($lastChar == '\\' || $lastChar == '(' || $lastChar == '{' || $lastChar == '[' || $lastChar == ',') {
                $opened = true;
                $command = rtrim($command, "\\");
            } else
                $opened = false;

            echo Colors::ENDC;
            if ($command == "exit") 
                exit();
            if (!$opened) {
                try {
                    if ( strpos($command, ";") === false && strpos($command, "return") === false && strpos($command, "echo") === false )
                        $command = "return ".$command;
                    
                    $return = eval("" . $command . ";");
                    $returnJSON = json_encode($return, JSON_PRETTY_PRINT);
                    echo "\n".self::beautifulOutput($return)."\n";
                    
                } catch (Exception $e) { 
                    echo Colors::BG_RED.Colors::BLUE." ".$e->getMessage()." ".Colors::ENDC."\n";
                }
            }
        }
    }

    public static function beautifulOutput($in, $indent = ""){
        $out = "";
        
        if (!isset($in) || $in === null) {
            $out = Colors::GRAY."null".Colors::ENDC;
        } else if (is_array($in) || is_object($in)) {
            $isObject = is_object($in);
            if ($isObject) {
                $out .= Colors::BLUE.get_class($in). Colors::ENDC ." ";
            }
            $out .= Colors::LIGHT_BLUE.($isObject ? "{" : "[").Colors::ENDC."\n";
            foreach ($in as $key => $value) {
                $out .= 
                    $indent."   ".self::beautifulOutput($key, $indent."   ").
                    Colors::YELLOW.": ".Colors::ENDC.
                    self::beautifulOutput($value, $indent."   ").
                    "\n";
            }
            $out .= $indent.Colors::LIGHT_BLUE.($isObject ? "}" : "]").Colors::ENDC;
        } else if(is_numeric($in)) {
            $out .= Colors::TURQUIOUS. $in.Colors::ENDC;
        } else if(is_string($in)) {
            $rand = rand(11111, 99999);
            // This happens if you don't want to parse it your own xD
            $out .= Colors::GREEN. str_replace("n--cn10".$rand."3e9--n", "\n", json_encode(str_replace("\n","n--cn10".$rand."3e9--n", $in))) .Colors::ENDC;
        } else if(is_bool($in)) {
            $out .= ($in ? Colors::GREEN : Colors::RED). json_encode($in).Colors::ENDC;
        } else {
            $out .= json_encode($in);
        }

        return $out;
    }


    public static function handleFatal(){
        //set_error_handler("error");
        //register_shutdown_function("fatal");
        $error = error_get_last();
        echo Colors::BG_RED.Colors::BLUE." ".$error["message"]." ".Colors::ENDC."\n";
        ReplCLI::replLoop();
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        echo Colors::BG_RED.Colors::BLUE." ".$errstr." ".Colors::ENDC."\n";
    }
}