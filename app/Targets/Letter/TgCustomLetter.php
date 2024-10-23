<?php

namespace Rapid\Mmb\PanelKit\Targets\Letter;

use Illuminate\Database\Eloquent\Model;
use Laravel\SerializableClosure\SerializableClosure;

class TgCustomLetter implements TgLetter
{

    public function __construct(
        public SerializableClosure $callback,
    )
    {
    }

    public function getLetter(Model $record) : array
    {
        return $this->callback->__invoke($record);
    }

}
