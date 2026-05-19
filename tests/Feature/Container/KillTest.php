<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

it('kills a running container with default signal', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);

    $this->docker->containers()->kill($created['Id']);

    $info = $this->docker->containers()->inspect($created['Id']);
    expect($info['State']['Running'])->toBeFalse()
        ->and($info['State']['ExitCode'])->not->toBe(0);
});

it('kills a container with custom signal', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sh', '-c', 'trap "exit 0" TERM; while true; do sleep 1; done'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);

    $this->docker->containers()->kill($created['Id'], signal: 'SIGTERM');
    $this->docker->containers()->wait($created['Id']);

    $info = $this->docker->containers()->inspect($created['Id']);
    expect($info['State']['Running'])->toBeFalse()
        ->and($info['State']['ExitCode'])->toBe(0);
});

it('throws when killing a non-existent container', function () {
    expect(fn () => $this->docker->containers()->kill('does-not-exist-'.uniqid()))
        ->toThrow(DockerException::class);
});
