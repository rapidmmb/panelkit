<?php

namespace Rapid\Mmb\PanelKit\Every;

use Closure;
use Illuminate\Database\Eloquent\Model;

class Every
{

    /**
     * Make new builder
     *
     * @return EveryBuilder
     */
    public static function make()
    {
        return new EveryBuilder();
    }

    /**
     * Make new builder sending to all
     *
     * @return EveryBuilder
     */
    public static function toAll()
    {
        return static::make()->toAll();
    }

    /**
     * Make new builder sending to custom records or query
     *
     * @param iterable|Model|int|string|Closure $to
     * @return EveryBuilder
     */
    public static function to(iterable|Model|int|string|Closure $to)
    {
        return static::make()->to($to);
    }

}
