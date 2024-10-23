<?php

namespace Rapid\Mmb\PanelKit\Targets\Letter;

use Illuminate\Database\Eloquent\Model;

class TgFixedLetter implements TgLetter
{

    public function __construct(
        public array $message,
    )
    {
    }

    public function getLetter(Model $record) : array
    {
        return $this->message;
    }

}
