<?php

namespace de\interaapps\ulole\core\cli;

class CLI {
    public array $commands = [];
    public array $descriptions = [];
    public array $applicationAttribs = [];

    /**
     * Change the not found errormessage
     */
    public string|null $errorMessage = null;
    /**
     * Shows a list with all commands on function not found error
     */
    public bool $showArgsOnError = true;


    /**
     * Register a new command
     * @param String function-name (Command)
     * @param Closure function (example:function() {return "Hello world";})
     * @param String (Optional) Description
     */
    public function register(string $name, $func, string $description = ""): void {
        $this->commands[$name] = $func;
        $this->descriptions[$name] = $description;
    }

    /**
     * Runs a command
     */
    public function run($args, $command = null): void {
        if ($command === null)
            $command = $args[0];
        if (isset($this->commands[$command])) {
            $function = ($this->commands[$command]);
            echo $function($args);
        } else {
            if ($this->errorMessage != null)
                echo $this->errorMessage;
            else
                echo Colors::PREFIX_ERROR . "Function \"" . $command . "\" not found!\n";


            if ($this->showArgsOnError) {
                $showArgs = Colors::PREFIX_DONE . "Those are some valid functions: ";
                foreach ($this->commands as $command => $value) {
                    $showArgs .= "\n  \033[92m- \033[0m" . $command . ": " . $this->descriptions[$command];
                }
                echo $showArgs . "\n";
            }

        }
    }

    public function getCommands(): array {
        return $this->commands;
    }

    public function getDescriptions(): array {
        return $this->descriptions;
    }

    public function getApplicationAttrib($i) {
        return $this->applicationAttribs[$i];
    }

    public function setApplicationAttrib($key, $value): CLI {
        $this->applicationAttribs[$key] = $value;
        return $this;
    }
}