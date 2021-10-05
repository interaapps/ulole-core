<?php
namespace de\interaapps\ulole\core\jobs;

interface Job {
    function run(JobHandler $jobHandler = null);
}