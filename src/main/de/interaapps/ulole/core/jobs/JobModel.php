<?php

namespace de\interaapps\ulole\core\jobs;

use de\interaapps\ulole\orm\attributes\Column;
use de\interaapps\ulole\orm\attributes\Table;
use de\interaapps\ulole\orm\ORMModel;

#[Table("uloleorm_jobs", disableAutoMigrate: true)]
class JobModel {
    use ORMModel;

    #[Column]
    public int $id;
    #[Column]
    public string $object;
    #[Column]
    public string $state;
    #[Column]
    public int $repeat;
    #[Column]
    public int $failed_count;
    #[Column]
    public string $created_at;

}