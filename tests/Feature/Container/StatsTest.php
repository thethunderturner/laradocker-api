<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

it('gets resource usage statistics from a running container', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '5'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);

    $stats = $this->docker->containers()->stats($created['Id'], stream: false);

    expect($stats)->toHaveKey('cpu_stats')
        ->and($stats)->toHaveKey('memory_stats')
        ->and($stats)->toHaveKey('blkio_stats');
});

it('supports one-shot mode', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '5'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);

    $stats = $this->docker->containers()->stats($created['Id'], stream: false, oneShot: true);

    expect($stats)->toHaveKey('cpu_stats');
});

it('throws for a non-existent container', function () {
    expect(fn () => $this->docker->containers()->stats('does-not-exist-'.uniqid()))
        ->toThrow(DockerException::class);
});
