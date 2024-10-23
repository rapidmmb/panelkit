<?php

namespace Rapid\Mmb\PanelKit\Lock;

use Mmb\Action\Section\Attributes\FixedDialog;
use Mmb\Action\Section\Controllers\CallbackControl;
use Mmb\Action\Section\Dialog;
use Mmb\Action\Section\Section;
use Mmb\Action\Update\UpdateHandling;
use Mmb\Core\Updates\Update;
use Mmb\Support\Format\KeyFormatter;
use Mmb\Support\Telegram\Keys;
use Rapid\Mmb\PanelKit\Models\LockRequire;

class LockRequest extends Section implements UpdateHandling
{
    use CallbackControl;

    public string $group;

    protected array|false $locks;

    public function isRequired(): bool
    {
        if (false !== $this->locks = Lock::checkLocks($this->group, $this->update))
        {
            return true;
        }

        return false;
    }

    public function main()
    {
        $this->dialog('mainDialog')->response();
    }

    #[FixedDialog('lock:{group:slug}')]
    public function mainDialog(Dialog $dialog)
    {
        $dialog->with('group');

        $this->locks ??= Lock::checkLocks($this->group, $this->update);
        $submit = __('panelkit::lock.submit');

        if ($dialog->isCreating())
        {
            $dialog
                ->schema(
                    KeyFormatter::value(
                        function () use($dialog)
                        {
                            if ($this->locks)
                            {
                                /** @var LockRequire $lock */
                                foreach ($this->locks as $lock)
                                {
                                    yield [Keys::url($lock->title, $lock->url)];
                                }
                            }
                        }
                    )
                );
        }

        $dialog
            ->message(fn () => __('panelkit::lock.error', ['submit' => $submit]))
            ->schema(
                [
                    [$dialog->keyId($submit, 'submit')],
                ]
            )
            ->on(
                'submit',
                function () use ($dialog)
                {
                    if ($this->locks)
                    {
                        $this->tell(__('panelkit::lock.submit_invalid'), alert: true);
                        $dialog->reload();
                    }
                    else
                    {
                        $this->update->getMessage()?->delete(ignore: true);
                    }
                }
            );
    }

    public static function for(string $group)
    {
        $instance = static::make();
        $instance->group = $group;

        return $instance;
    }

    public function handleUpdate(Update $update)
    {
        if ($this->isRequired())
        {
            $this->main();
        }
        else
        {
            $update->skipHandler();
        }
    }

    public function required()
    {
        if ($this->isRequired())
        {
            $this->main();
            $this->update->stopHandling();
        }
    }
}