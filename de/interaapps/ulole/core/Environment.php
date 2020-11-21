<?php
namespace de\interaapps\ulole\core;

use de\interaapps\ulole\core\config\Configuration;

class Environment {
    private $production;
    private $config;
    
    public function __construct() {
        $this->config = new Configuration();
    }
    
    public static function fromCurrent() : Environment {
        $env = new Environment();
        return $env;
    }
    
    public function isProduction() : bool {
        return $this->production;
    }

    public function setProduction($production) {
        $this->production = $production;
        return $this;
    }

    public function getConfig() : Configuration {
        return $this->config;
    }
}