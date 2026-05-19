<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

it('fails with invalid credentials', function () {
    expect(fn () => $this->docker->system()->auth('invalid', 'invalid', 'https://invalid.registry'))
        ->toThrow(DockerException::class);
});
