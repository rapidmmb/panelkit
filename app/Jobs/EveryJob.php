<?php

namespace Rapid\Mmb\PanelKit\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Rapid\Mmb\PanelKit\Every\EveryLogger;
use Rapid\Mmb\PanelKit\Targets\Aim\TgAim;
use Rapid\Mmb\PanelKit\Targets\Letter\TgLetter;
use Rapid\Mmb\PanelKit\Targets\Notifier\TgNotifier;

class EveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $cachedCount = 0;

    public function __construct(
        public TgAim        $aim,
        public TgNotifier   $notifier,
        public TgLetter     $letter,
        public ?EveryLogger $logger = null,
        public int          $offset = 0,
        public int          $successCount = 0,
        public int          $failedCount = 0,
    )
    {
    }

    public const TIMEOUT            = 30;
    public const RECORDS_PER_HANDLE = 100;
    public const RECORDS_PER_QUERY  = 10;
    public const SLEEP_PER_QUERY    = 300000;

    /**
     * Execute the job.
     */
    public function handle() : void
    {
        $this->cachedCount = $this->aim->getQuery()->count();

        if ($this->offset == 0)
        {
            $this->logger?->created($this);
        }

        $done = 0;
        $startTime = time();

        while ($done < self::RECORDS_PER_HANDLE && $this->checkRemains() > 0 && time() - $startTime < self::TIMEOUT)
        {
            $records = $this->aim->getQuery()->offset($this->offset)->take(self::RECORDS_PER_QUERY)->get();

            $this->notifyRecords($records);

            $done += $records->count();

            usleep(self::SLEEP_PER_QUERY);
        }

        if ($this->checkRemains() > 0)
        {
            dispatch(
                new EveryJob(
                    $this->aim,
                    $this->notifier,
                    $this->letter,
                    $this->logger,
                    $this->offset,
                    $this->successCount,
                    $this->failedCount
                )
            )
                ->delay(now()->seconds(59));
            $this->logger?->log($this);
        }
        else
        {
            $this->logger?->completed($this);
        }
    }

    public function notifyRecords(Collection $records) : void
    {
        foreach ($records as $record)
        {
            $this->notifyRecord($record);

            $this->offset++;
        }
    }

    public function notifyRecord(Model $record) : void
    {
        try
        {
            if ($this->notifier->notify($record, $this->letter->getLetter($record)))
            {
                $this->successCount++;
            }
            else
            {
                $this->failedCount++;
            }
        }
        catch (\Throwable $exception)
        {
            $this->failedCount++;
            report($exception);
            $this->logger?->error($this, $exception);
        }
    }

    // protected function checkTimeout()
    // {
    //     return time() - LARAVEL_START > 30;
    // }

    public function checkRemains() : int
    {
        return $this->cachedCount - $this->offset;
    }

}
