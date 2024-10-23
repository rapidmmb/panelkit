<?php

namespace Rapid\Mmb\PanelKit\Mmb\Sections\Admin\Every;

use Mmb\Action\Form\Form;

abstract class EveryForm extends Form
{

    /**
     * Notify the message
     *
     * @return void
     */
    protected abstract function notify();

    /**
     * Default ok message
     *
     * @return void
     */
    protected abstract function ok();

    protected function onFinish()
    {
        $this->notify();

        $this->ok();
        $this->back();
    }

}
