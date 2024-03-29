<?php

namespace de\interaapps\ulole\core\config;

class Configuration {
    private array $configEntries;

    public function __construct() {

    }

    public function loadPHPFile($file, $parent = null): Configuration {
        if (file_exists($file)) {
            $this->insertRecursive(include($file), $parent);
        }
        return $this;
    }

    public function loadENV(): Configuration {
        $parsed = [];
        foreach (getenv() as $key => $val)
            $parsed[str_replace("_", ".", $key)] = $val;
        $this->insertRecursive($parsed);
        return $this;
    }

    public function loadENVFile($file, $parent = null): Configuration {
        if (file_exists($file)) {
            $envFile = file_get_contents($file);

            $parsed = [];

            foreach (explode("\n", $envFile) as $line) {
                if (str_contains($line, "=")) {
                    [$key, $value] = explode("=", $line, 2);
                    $parsed[str_replace("_", ".", $key)] = $value;
                }
            }
            $this->insertRecursive($parsed, $parent);
        }
        return $this;
    }

    public function loadJSONFile($file, $parent = null): Configuration {
        if (file_exists($file)) {
            $this->insertRecursive(json_decode(file_get_contents($file)), $parent);
        }
        return $this;
    }

    private function insertRecursive($arr, $parent = null): void {
        if (is_object($arr))
            $arr = (array)$arr;

        $parentString = '';
        if ($parent !== null)
            $parentString = $parent . '.';

        foreach ($arr as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $this->insertRecursive($value, $parentString . strtolower($key));
            } else {
                $this->configEntries[$parentString . (strtolower($key))] = $value;
            }
        }
    }

    public function getConfigEntries() {
        return $this->configEntries;
    }


    public function get($entry, $default = null) {
        if (!isset($this->configEntries[$entry]))
            return $default;
        return $this->configEntries[$entry];
    }


    public function set($entry, $value): Configuration {
        $this->configEntries[$entry] = $value;
        return $this;
    }
}