<?php
namespace de\interaapps\ulole\core\jobs;

class JobHandler
{
    private $database;
    private $mode = "database";

    public function __construct($database = "main") {
        $this->database = $database;
    }

    public function push(Job $job, $repeat = 3): bool {
        if ($this->mode == "database") {
            $jobModel = new JobModel();
            $jobModel->object = serialize($job);
            $jobModel->state = "OPEN";
            $jobModel->repeat = $repeat;
            return $jobModel->save();
        } else if ($this->mode == "sync") {
            $tries = 0;
            while ($tries++ < $repeat) {
                if ($this->runNow($job))
                    return true;
            }
        }
        return false;
    }

    public function runNow(Job $job) : bool {
        try {
            $job->run($this);
            return true;
        } catch (\Exception $e) {}
        return false;
    }

    public function getOpened(): array {
        return JobModel::table($this->database)->where("state", "OPEN")->orWhere("state", "FAILED")->all();
    }

    public function handleAll() : array {
        $errors = [];
        foreach ($this->getOpened() as $jobModel) {
            $jobModel->state = "RUNNING";
            $jobModel->save($this->database);
            if ($jobModel->failed_count++ < $jobModel->repeat) {
                try {
                    $job = unserialize($jobModel->object);
                    $job->run($this);

                    $jobModel->delete();
                } catch (\Exception $e) {
                    $errors[] = $e;
                    $jobModel->state = "FAILED";
                    $jobModel->save($this->database);
                }
            }
        }
        return $errors;
    }

    /**
     * @param string $mode
     * @return JobHandler
     */
    public function setMode(string $mode): JobHandler
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }
}