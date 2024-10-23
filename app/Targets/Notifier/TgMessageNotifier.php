<?php

namespace Rapid\Mmb\PanelKit\Targets\Notifier;

use Illuminate\Database\Eloquent\Model;

class TgMessageNotifier implements TgNotifier
{

    public function notify(Model $record, array $message) : bool
    {
        return (bool) bot()->sendMessage(
            $message,
            chat  : $record->id,
            ignore: true,
        );
    }

}
