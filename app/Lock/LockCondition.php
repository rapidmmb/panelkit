<?php

namespace Rapid\Mmb\PanelKit\Lock;

interface LockCondition
{

    public function show() : bool;

}