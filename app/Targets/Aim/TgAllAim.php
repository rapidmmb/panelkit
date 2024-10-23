<?php

namespace Rapid\Mmb\PanelKit\Targets\Aim;

use Illuminate\Contracts\Database\Query\Builder;
use Rapid\Mmb\PanelKit\PanelKit;

class TgAllAim implements TgAim
{

    public function getQuery() : Builder
    {
        return (PanelKit::getUserClass())::query()->orderBy('created_at');
    }

}
