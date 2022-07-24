<?php

namespace de\interaapps\ulole\core\testing\factory;

abstract class Factory {
    public string $model;
    private Faker $faker;
    private bool $logging = false;

    public function __construct() {
        $this->faker = new Faker();
    }

    public function produce(int $amount = 1): Factory {
        foreach (range(0, $amount - 1) as $i) {
            $assemblyLine = new AssemblyLine($this);
            $this->production($assemblyLine);
        }
        return $this;
    }

    public static function run($amount = 1): static {
        return (new static())->produce($amount);
    }

    protected abstract function production(AssemblyLine $assemblyLine): void;

    public function getModel(): string {
        return $this->model;
    }

    /**
     * @param class-string $model
     * @return $this
     */
    public function setModel(string $model) {
        $this->model = $model;
        return $this;
    }

    public function getFaker(): Faker {
        return $this->faker;
    }

    public function isLogging(): bool {
        return $this->logging;
    }

    public function setLogging($logging): Factory {
        $this->logging = $logging;
        return $this;
    }
}