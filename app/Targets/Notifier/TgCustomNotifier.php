<?php

namespace Rapid\Mmb\PanelKit\Targets\Notifier;

use Illuminate\Database\Eloquent\Model;
use Laravel\SerializableClosure\SerializableClosure;

class TgCustomNotifier implements TgNotifier
{

    public function __construct(
        public SerializableClosure $callback,
    )
    {
    }

    public function notify(Model $record, array $message) : bool
    {
        $result = $this->callback->__invoke($record, $message);

        if ($result instanceof Model)
        {
            $result = bot()->sendMessage(
                $message,
                chat  : $result->id,
                ignore: true,
            );
        }

        return (bool) $result;
    }

}
