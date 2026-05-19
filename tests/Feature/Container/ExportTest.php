<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

it('exports a container as a tarball', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['echo', 'hello'],
    ]);
    $this->cleanup[] = $created['Id'];

    $tarball = $this->docker->containers()->export($created['Id']);

    // Export returns a tarball; verify it's non-empty.
    expect($tarball)->toBeString()
        ->and(strlen($tarball))->toBeGreaterThan(0);
});

it('throws for a non-existent container', function () {
    expect(fn () => $this->docker->containers()->export('does-not-exist-'.uniqid()))
        ->toThrow(DockerException::class);
});
