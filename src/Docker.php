<?php

namespace TheThunderTurner\Docker;

use TheThunderTurner\Docker\Resources\Containers;

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
}
