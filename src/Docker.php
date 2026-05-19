<?php

namespace TheThunderTurner\Docker;

use TheThunderTurner\Docker\Resources\Containers;
use TheThunderTurner\Docker\Resources\System;

class Docker
{
    private Transport $transport;

    public function __construct(?Transport $transport = null)
    {
        $this->transport = $transport ?? new Transport;
    }

    public function containers(): Containers
    {
        return new Containers($this->transport);
    }

    public function system(): System
    {
        return new System($this->transport);
    }
}
