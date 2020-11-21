<?php
namespace de\interaapps\ulole\core\traits;

trait JSONModel {
    public function json(){
        return json_encode($this);
    }

    public static function fromJson($json){
        $instance = new static();
        $decoded = json_decode($json);

        foreach (get_object_vars($instance) as $field => $ignored) {
            if (isset($decoded->{$field}))
                $instance->{$field} = $decoded->{$field};
        }

        return $instance;
    }
}