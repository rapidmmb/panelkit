<?php

namespace Rapid\Mmb\PanelKit\Every;

use Rapid\Mmb\PanelKit\Jobs\EveryJob;

interface EveryLogger
{

    /**
     * Log creation event
     *
     * @param EveryJob $job
     * @return void
     */
    public function created(EveryJob $job) : void;

    /**
     * Log the status for each partitions
     *
     * @param EveryJob $job
     * @return void
     */
    public function log(EveryJob $job) : void;

    /**
     * Log the notifier errors (unexpected but possible)
     *
     * @param EveryJob   $job
     * @param \Throwable $exception
     * @return void
     */
    public function error(EveryJob $job, \Throwable $exception) : void;

    /**
     * Log the completed status
     *
     * @param EveryJob $job
     * @return void
     */
    public function completed(EveryJob $job) : void;

}
