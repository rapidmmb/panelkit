<?php

namespace Rapid\Mmb\PanelKit\Mmb\Sections\Admin\Every;

use Mmb\Action\Form\Input;
use Rapid\Mmb\PanelKit\Every\Every;

class EveryForwardForm extends EveryForm
{

    protected $inputs = [
        'message',
    ];

    public function message(Input $input)
    {
        $input
            ->prompt(__('panelkit::tg-notification.request_forward_message'))
            ->messageBuilder();
    }

    protected function ok()
    {
        $this->response(__('panelkit::tg-notification.end_forward_message'));
    }

    protected function notify()
    {
        Every::toAll()
            ->forward($this->update->getChat()->id, $this->update->message->id)
            ->log($this->update->getChat()->id)
            ->notify();
    }

}
