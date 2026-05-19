<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

it('updates container resource limits', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);

    $this->docker->containers()->update($created['Id'], [
        'Memory' => 134217728,      // 128 MB
        'MemorySwap' => 268435456,  // 256 MB
    ]);

    $info = $this->docker->containers()->inspect($created['Id']);
    expect($info['HostConfig']['Memory'])->toBe(134217728)
        ->and($info['HostConfig']['MemorySwap'])->toBe(268435456);
});

it('throws when updating a non-existent container', function () {
    expect(fn () => $this->docker->containers()->update('does-not-exist-'.uniqid(), ['Memory' => 134217728]))
        ->toThrow(DockerException::class);
});
