<?php

namespace Rapid\Mmb\PanelKit\Targets\Letter;

use Illuminate\Database\Eloquent\Model;

interface TgLetter
{

    public function getLetter(Model $record) : array;

}
