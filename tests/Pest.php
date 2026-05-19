<?php

use TheThunderTurner\Docker\Docker;
use TheThunderTurner\Docker\Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(function (): void {
        $this->docker = new Docker;
        $this->cleanup = [];
    })
    ->afterEach(function (): void {
        foreach ($this->cleanup as $id) {
            $this->docker->containers()->remove($id, force: true);
        }
    })
    ->in(__DIR__);
