<?php
namespace de\interaapps\ulole\core\jobs;

use de\interaapps\ulole\orm\ORMModel;

class JobModel {
    use ORMModel;

    public $id;
    public $object;
    public $state;
    public $repeat;
    public $failed_count;
    public $created_at;

}