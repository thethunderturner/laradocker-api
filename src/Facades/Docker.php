<?php

namespace TheThunderTurner\Docker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TheThunderTurner\Docker\Docker
 */
class Docker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TheThunderTurner\Docker\Docker::class;
    }
}
