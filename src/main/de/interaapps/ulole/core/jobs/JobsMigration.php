<?php

namespace de\interaapps\ulole\core\jobs;


use de\interaapps\ulole\orm\Database;
use de\interaapps\ulole\orm\migration\Blueprint;
use de\interaapps\ulole\orm\migration\Migration;

class JobsMigration implements Migration {
    private $tableName = "uloleorm_jobs";

    public function up(Database $database) {
        return $database->create($this->tableName, function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string("object");
            $blueprint->enum("state", ["OPEN", "RUNNING", "FAILED"])->default('OPEN');
            $blueprint->int("repeat")->default(3);
            $blueprint->int("failed_count")->default(0);
            $blueprint->timestamp("created_at")->currentTimestamp();
        });
    }

    public function down(Database $database) {
        return $database->drop($this->tableName);

    }

    public function setTableName($tableName): void {
        $this->tableName = $tableName;
    }
}