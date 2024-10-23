<?php

namespace Rapid\Mmb\PanelKit\Targets\Notifier;

use Illuminate\Database\Eloquent\Model;

interface TgNotifier
{

    /**
     * Fire notification for an entity
     *
     * @param Model $record
     * @param array $message
     * @return bool
     */
    public function notify(Model $record, array $message) : bool;

}
