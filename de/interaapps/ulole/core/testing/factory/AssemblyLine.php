<?php
namespace de\interaapps\ulole\core\testing\factory;

class AssemblyLine {
    private $factory;
    public function __construct(Factory $factory) {
        $this->factory = $factory;
    }

    public function insert($closure){
        $faker = $this->factory->getFaker();
        $clazz = $this->factory->getModel();
        $obj = new $clazz();
        $closure($obj, $faker);
        $result = $obj->save();
        if ($this->factory->isLogging())
            echo "Inserted: ".$result ? 'true' : 'false';
    }
}