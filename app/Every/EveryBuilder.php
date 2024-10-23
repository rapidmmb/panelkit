<?php

namespace Rapid\Mmb\PanelKit\Every;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Traits\Conditionable;
use Laravel\SerializableClosure\SerializableClosure;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Core\Updates\Update;
use Rapid\Mmb\PanelKit\Jobs\EveryJob;
use Rapid\Mmb\PanelKit\PanelKit;
use Rapid\Mmb\PanelKit\Targets\Aim\TgAim;
use Rapid\Mmb\PanelKit\Targets\Aim\TgAllAim;
use Rapid\Mmb\PanelKit\Targets\Aim\TgCustomAim;
use Rapid\Mmb\PanelKit\Targets\Letter\TgCustomLetter;
use Rapid\Mmb\PanelKit\Targets\Letter\TgEmptyLetter;
use Rapid\Mmb\PanelKit\Targets\Letter\TgFixedLetter;
use Rapid\Mmb\PanelKit\Targets\Letter\TgLetter;
use Rapid\Mmb\PanelKit\Targets\Notifier\TgCustomNotifier;
use Rapid\Mmb\PanelKit\Targets\Notifier\TgForwardNotifier;
use Rapid\Mmb\PanelKit\Targets\Notifier\TgMessageNotifier;
use Rapid\Mmb\PanelKit\Targets\Notifier\TgNotifier;

class EveryBuilder
{
    use Conditionable;

    protected TgNotifier  $notifier;
    protected TgLetter    $letter;
    protected TgAim       $aim;
    protected EveryLogger $logger;

    /**
     * Set notifier instance
     *
     * @param TgNotifier|Closure $notifier
     * @return $this
     */
    public function notifier(TgNotifier|Closure $notifier)
    {
        if ($notifier instanceof Closure)
        {
            $notifier = new TgCustomNotifier(new SerializableClosure($notifier));
        }

        $this->notifier = $notifier;
        return $this;
    }

    /**
     * Set letter instance
     *
     * @param TgLetter $letter
     * @return $this
     */
    public function letter(TgLetter $letter)
    {
        $this->letter = $letter;
        return $this;
    }

    /**
     * Set aim instance
     *
     * @param TgAim $aim
     * @return $this
     */
    public function aim(TgAim $aim)
    {
        $this->aim = $aim;
        return $this;
    }

    /**
     * Set logger instance
     *
     * @param EveryLogger $logger
     * @return $this
     */
    public function logger(EveryLogger $logger)
    {
        $this->logger = $logger;
        return $this;
    }


    /**
     * Set aim to all records
     *
     * @return $this
     */
    public function toAll()
    {
        return $this->aim(new TgAllAim());
    }

    /**
     * Set target to custom records or query
     *
     * @param iterable|Model|int|string|Closure $to
     * @return $this
     */
    public function to(iterable|Model|int|string|Closure $to)
    {
        if (is_string($to) || is_int($to) || $to instanceof Model)
        {
            $to = [$to];
        }

        if (is_iterable($to))
        {
            $class = null;
            $ids = [];
            foreach ($to as $record)
            {
                if ($record instanceof Model)
                {
                    if ($class === null)
                    {
                        $class = get_class($record);
                    }
                    elseif ($class != get_class($record))
                    {
                        throw new \InvalidArgumentException("Can't parse multiple model types");
                    }

                    $ids[] = $record->getKey();
                }
                else
                {
                    $ids[] = $record;
                }
            }

            $to = function () use ($class, $ids)
            {
                return ($class ?? PanelKit::getUserClass())::whereIn($ids)->orderBy('created_at');
            };
        }

        return $this->aim(new TgCustomAim(new SerializableClosure($to)));
    }


    /**
     * Set the notifier to send mode
     *
     * @param array|null $message
     * @return $this
     */
    public function send(?array $message = null)
    {
        $this->notifier(new TgMessageNotifier());

        if (isset($message))
        {
            $this->message($message);
        }

        return $this;
    }

    /**
     * Set the notifier to forward mode
     *
     * @param $chatId
     * @param $messageId
     * @return EveryBuilder
     */
    public function forward($chatId, $messageId)
    {
        return $this
            ->notifier(new TgForwardNotifier($chatId, $messageId))
            ->letter(new TgEmptyLetter());
    }


    /**
     * Set the message
     *
     * @param array|Closure $message
     * @return $this
     */
    public function message(array|Closure $message)
    {
        return $this->letter(
            $message instanceof Closure ?
                new TgCustomLetter(new SerializableClosure($message)) :
                new TgFixedLetter($message)
        );
    }


    /**
     * Set logging to a chat
     *
     * @param string|int|ChatInfo|Message|Update $chat
     * @return $this
     */
    public function log(string|int|ChatInfo|Message|Update $chat)
    {
        $chat = match (true)
        {
            $chat instanceof Update   => $chat->getChat()->id,
            $chat instanceof Message  => $chat->chat->id,
            $chat instanceof ChatInfo => $chat->id,
            default                   => $chat,
        };

        return $this->logger(new PvEveryLogger($chat));
    }


    /**
     * Dispatch the notification
     *
     * @return PendingDispatch
     */
    public function notify()
    {
        return dispatch(
            new EveryJob(
                $this->aim ?? new TgAllAim(),
                $this->notifier ?? new TgMessageNotifier(),
                $this->letter ?? new TgEmptyLetter(),
                $this->logger ?? null,
            )
        );
    }

}
