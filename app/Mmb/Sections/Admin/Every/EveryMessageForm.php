<?php

namespace Rapid\Mmb\PanelKit\Mmb\Sections\Admin\Every;

use Mmb\Action\Form\Input;
use Rapid\Mmb\PanelKit\Every\Every;

class EveryMessageForm extends EveryForm
{

    protected $inputs = [
        'message',
    ];

    public function message(Input $input)
    {
        $input
            ->prompt(__('panelkit::tg-notification.request_send_message'))
            ->messageBuilder();
    }

    protected function ok()
    {
        $this->response(__('panelkit::tg-notification.end_send_message'));
    }

    protected function notify()
    {
        Every::toAll()
            ->send($this->message->toArray())
            ->log($this->update->getChat()->id)
            ->notify();
    }

}
