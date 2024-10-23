<?php

namespace Rapid\Mmb\PanelKit\Targets\Aim;

use Illuminate\Contracts\Database\Query\Builder;

interface TgAim
{

    public function getQuery() : Builder;

}
