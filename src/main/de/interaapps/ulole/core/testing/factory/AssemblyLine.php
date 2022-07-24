<?php

namespace de\interaapps\ulole\core\testing\factory;

class AssemblyLine {
    private array $results = [];

    public function __construct(private Factory $factory) {
    }

    public function insert($closure): void {
        $faker = $this->factory->getFaker();
        $clazz = $this->factory->getModel();
        $obj = new $clazz();
        $closure($obj, $faker);
        $result = $obj->save();
        $this->results[] = $result;
        if ($this->factory->isLogging())
            echo "Inserted: " . $result ? 'true' : 'false';
    }

    public function getResults(): array {
        return $this->results;
    }
}