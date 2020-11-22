<?php
namespace de\interaapps\ulole\core\testing\factory;

class AssemblyLine {
    private $factory;
    private $results;
    public function __construct(Factory $factory) {
        $this->factory = $factory;
        $this->results = [];
    }

    public function insert($closure){
        $faker = $this->factory->getFaker();
        $clazz = $this->factory->getModel();
        $obj = new $clazz();
        $closure($obj, $faker);
        $result = $obj->save();
        array_push($this->results, $result);
        if ($this->factory->isLogging())
            echo "Inserted: ".$result ? 'true' : 'false';
    }

    public function getResults() {
        return $this->results;
    }
}