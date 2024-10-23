<?php

namespace Rapid\Mmb\PanelKit\Targets\Notifier;

use Illuminate\Database\Eloquent\Model;

class TgForwardNotifier implements TgNotifier
{

    public function __construct(
        public $chatId,
        public $messageId,
    )
    {
    }

    public function notify(Model $record, array $message) : bool
    {
        return (bool) bot()->forwardMessage(
            $message,
            from   : $this->chatId,
            message: $this->messageId,
            chat   : $record->id,
            ignore : true,
        );
    }

}
