<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

it('resizes the TTY of a running container', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '10'],
        'Tty' => true,
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);

    $this->docker->containers()->resize($created['Id'], 40, 120);

    // Resize returns void on success; no exception means it passed.
    expect(true)->toBeTrue();
});

it('throws for a non-existent container', function () {
    expect(fn () => $this->docker->containers()->resize('does-not-exist-'.uniqid(), 40, 120))
        ->toThrow(DockerException::class);
});
