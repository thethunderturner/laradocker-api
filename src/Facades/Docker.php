<?php

namespace TheThunderTurner\Docker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array listContainers(bool $all = true)
 * @method static array stopContainer(string $id)
 * @method static array restartContainer(string $id)
 * @method static array createContainer(array $config)
 * @method static string containerLogs(string $id, bool $stdout = true, bool $stderr = true, bool $timestamps = false, ?int $tail = null)
 *
 * @see \TheThunderTurner\Docker\Docker
 */
class Docker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TheThunderTurner\Docker\Docker::class;
    }
}
