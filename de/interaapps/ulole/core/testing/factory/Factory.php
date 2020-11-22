<?php
namespace de\interaapps\ulole\core\testing\factory;

abstract class Factory {
    public $model;
    private $faker;
    private $logging = false;

    public function __construct() {
        $this->faker = new Faker();
    }

    public function produce(int $amount = 1){
        foreach (range(0, $amount-1) as $i) {
            $assemblyLine = new AssemblyLine($this);
            $this->production($assemblyLine);
        }
        return $this;
    }
    
    public static function run($amount = 1){
        return (new static())->produce($amount);
    }

    protected abstract function production(AssemblyLine $assemblyLine);

    public function getModel() {
        return $this->model;
    }

    public function setModel($model) {
        $this->model = $model;
        return $this;
    }

    
    public function getFaker() {
        return $this->faker;
    }

    public function isLogging() {
        return $this->logging;
    }

    public function setLogging($logging) {
        $this->logging = $logging;

        return $this;
    }
}