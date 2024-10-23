<?php

namespace Rapid\Mmb\PanelKit\Targets\Letter;

use Illuminate\Database\Eloquent\Model;

class TgEmptyLetter implements TgLetter
{

    public function getLetter(Model $record) : array
    {
        return [];
    }

}
