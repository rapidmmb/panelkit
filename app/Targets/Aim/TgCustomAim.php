<?php

namespace Rapid\Mmb\PanelKit\Targets\Aim;

use Illuminate\Contracts\Database\Query\Builder;
use Laravel\SerializableClosure\SerializableClosure;

class TgCustomAim implements TgAim
{

    public function __construct(
        public SerializableClosure $callback,
    )
    {
    }

    public function getQuery() : Builder
    {
        return $this->callback->__invoke();
    }

}
