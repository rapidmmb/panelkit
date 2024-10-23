<?php

namespace Rapid\Mmb\PanelKit\Lock;

use Carbon\Carbon;
use Mmb\Core\Updates\Update;
use Rapid\Mmb\PanelKit\Models\LockRequire;
use Rapid\Mmb\PanelKit\Models\LockStatus;

class Lock
{

    public static function add(
        bool            $is_public,
        mixed           $chat_id,
        string          $url,
        string          $title,
        ?string         $group = null,
        bool            $is_fake = false,
        null|int|Carbon $cache_pass = null,
        null|int|Carbon $accept_delay = null,
        ?int            $member_limit_until = null,
        ?Carbon         $expire_at = null,
    )
    {
        if ($cache_pass instanceof Carbon)
        {
            $cache_pass = $cache_pass->diff(now())->totalSeconds;
        }

        if ($accept_delay instanceof Carbon)
        {
            $accept_delay = $accept_delay->diff(now())->totalSeconds;
        }

        return LockRequire::create(
            [
                'group'              => $group ?? 'main',
                'is_public'          => $is_public,
                'chat_id'            => $chat_id,
                'url'                => $url,
                'title'              => $title,
                'is_fake'            => $is_fake,
                'cache_pass'         => $cache_pass,
                'accept_delay'       => $accept_delay,
                'member_limit_until' => $member_limit_until,
                'expire_at'          => $expire_at,
            ]
        );
    }

    public static function checkLocks(string $group, Update $update) : array|false
    {
        $isLocked = false;
        $locks = [];

        foreach (LockRequire::where('group', $group)->get() as $require)
        {
            switch (static::checkALock($require, $update))
            {
                case self::LOCKED:
                    $isLocked = true;
                    $locks[] = $require;
                    break;

                case self::VISIBLE_RELEASED:
                    $locks[] = $require;
                    break;
            }
        }

        foreach (config('panelkit.lock.fixed', []) as $info)
        {
            switch (static::checkJoiningIn($info['chat_id'], $update))
            {
                case self::LOCKED:
                    $isLocked = true;
                    $locks[] = new LockRequire($info);
                    break;

                case self::VISIBLE_RELEASED:
                    $locks[] = new LockRequire($info);
                    break;
            }
        }

        return $isLocked ? $locks : false;
    }

    public const LOCKED           = 0;
    public const RELEASED         = 1;
    public const VISIBLE_RELEASED = 2;

    public static function checkALock(LockRequire $require, Update $update) : int
    {
        // Expiration
        if ($require->expire_at && $require->expire_at->isPast())
        {
            $require->delete();
            return self::RELEASED;
        }

        // Member limitation
        if (
            $require->member_limit_until &&
            bot()->getChatMemberCount(chat: $require->chat_id, ignore: true) >= $require->member_limit_until
        )
        {
            $require->delete();
            return self::RELEASED;
        }

        // Fake mode
        if ($require->is_fake)
        {
            return self::VISIBLE_RELEASED;
        }

        // Cache system
        if ($require->cache_pass)
        {
            $status = LockStatus::query()
                ->where('lock_require_id', $require->id)
                ->where('unique_id', $update->getUser()->id)
                ->firstOrCreate([
                    'lock_require_id' => $require->id,
                    'unique_id' => $update->getUser()->id,
                    'passed_at' => null,
                ]);

            if ($status->passed_at?->addSectonds($require->cache_pass)->isFuture())
            {
                return self::RELEASED;
            }

            if (($result = self::checkJoiningIn($require, $update)) == self::RELEASED)
            {
                $status->passed_at = now();
                $status->save();
            }

            return $result;
        }

        return self::checkJoiningIn($require->chat_id, $update);
    }

    public static function checkJoiningIn($chatId, Update $update) : int
    {
        try
        {
            $member = bot()->getChatMember(
                chat: $chatId,
                user: $update->getUser()?->id,
            );

            return $member->isJoined ? self::RELEASED : self::LOCKED;
        }
        catch (\Throwable $e)
        {
            return self::VISIBLE_RELEASED;
        }
    }

}
