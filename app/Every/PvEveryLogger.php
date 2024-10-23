<?php

namespace Rapid\Mmb\PanelKit\Every;

use Rapid\Mmb\PanelKit\Jobs\EveryJob;

class PvEveryLogger implements EveryLogger
{

    public function __construct(
        public $chatId,
        public $messageId = null,
    )
    {
    }

    public function created(EveryJob $job) : void
    {
        $this->messageId = bot()->sendMessage(
            chat  : $this->chatId,
            ignore: true,
            text  : $this->getText('created', $job),
        )?->id;
    }

    public function log(EveryJob $job) : void
    {
        if ($this->messageId)
        {
            bot()->editMessageText(
                chat   : $this->chatId,
                message: $this->messageId,
                ignore : true,
                text   : $this->getText('progressing', $job),
            );
        }
    }

    public function error(EveryJob $job, \Throwable $exception) : void
    {
        if ($this->messageId)
        {
            bot()->sendMessage(
                chat  : $this->chatId,
                ignore: true,
                text  : __('panelkit::tg-notification.log.error_template', ['message' => $exception->getMessage()]),
            );
        }
    }

    public function completed(EveryJob $job) : void
    {
        if ($this->messageId)
        {
            bot()->editMessageText(
                chat   : $this->chatId,
                message: $this->messageId,
                ignore : true,
                text   : $this->getText('completed', $job),
            );
        }
    }

    protected function getText(string $status, EveryJob $job)
    {
        $title = __('panelkit::tg-notification.log.title.' . $status);
        $all = __('panelkit::tg-notification.log.counter.all', ['number' => $job->offset, 'all' => $job->cachedCount]);
        $success = __('panelkit::tg-notification.log.counter.success', ['number' => $job->successCount]);
        $failed = __('panelkit::tg-notification.log.counter.failed', ['number' => $job->failedCount]);
        $progress = $this->getProgress($job);

        return __(
            'panelkit::tg-notification.log.template',
            compact('title', 'all', 'success', 'failed', 'progress')
        );
    }

    protected function getProgress(EveryJob $job)
    {
        // [                       ] 0%
        // [|||||||||||            ] 50%
        // [|||||||||||||||||||||||] 100%

        $a = round($job->offset / $job->cachedCount * 24);
        $b = 24 - $a;
        $percent = round($job->offset / $job->cachedCount * 100);

        return "[" . ($a ? str_repeat('|', $a) : '') . ($b ? str_repeat(' ', $b) : '') . "] $percent%";
    }

}
