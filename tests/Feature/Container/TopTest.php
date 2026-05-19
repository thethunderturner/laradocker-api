<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

it('lists processes running inside a container', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);

    $top = $this->docker->containers()->top($created['Id']);

    expect($top)->toHaveKey('Titles')
        ->and($top)->toHaveKey('Processes')
        ->and($top['Titles'])->toBeArray()
        ->and($top['Processes'])->toBeArray()
        ->and($top['Processes'])->not->toBeEmpty();

    $allProcessesText = implode(' ', array_map(
        fn ($row) => implode(' ', $row),
        $top['Processes']
    ));
    expect($allProcessesText)->toContain('sleep');
});

it('top accepts custom ps args', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start(id: $created['Id']);

    $top = $this->docker->containers()->top($created['Id'], psArgs: 'aux');

    $titles = $top['Titles'];
    expect($titles)->toContain('USER');
});

it('top throws when container is not running', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
    ]);
    $this->cleanup[] = $created['Id'];

    // Container created but never started — top should fail (409 Conflict)
    expect(fn () => $this->docker->containers()->top($created['Id']))
        ->toThrow(DockerException::class);
});

it('top throws for non-existent container', function () {
    expect(fn () => $this->docker->containers()->top('does-not-exist-'.uniqid()))
        ->toThrow(DockerException::class);
});
