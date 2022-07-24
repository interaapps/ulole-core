<?php

namespace de\interaapps\ulole\core;

use de\interaapps\ulole\core\config\Configuration;

class Environment {
    private bool $production;
    private Configuration $config;

    public function __construct() {
        $this->config = new Configuration();
    }

    public static function fromCurrent(): Environment {
        return new Environment();
    }

    public function isProduction(): bool {
        return $this->production;
    }

    public function setProduction($production): Environment {
        $this->production = $production;
        return $this;
    }

    public function getConfig(): Configuration {
        return $this->config;
    }
}