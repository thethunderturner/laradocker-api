<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

describe('pause()', function () {
    it('pauses a running container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '30'],
        ]);
        $this->cleanup[] = $created['Id'];
        $this->docker->containers()->start($created['Id']);

        $this->docker->containers()->pause($created['Id']);

        $info = $this->docker->containers()->inspect($created['Id']);
        expect($info['State']['Paused'])->toBeTrue();
    });

    it('throws when pausing a non-existent container', function () {
        expect(fn () => $this->docker->containers()->pause('does-not-exist-'.uniqid()))
            ->toThrow(DockerException::class);
    });
});

describe('unpause()', function () {
    it('unpauses a paused container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '30'],
        ]);
        $this->cleanup[] = $created['Id'];
        $this->docker->containers()->start($created['Id']);
        $this->docker->containers()->pause($created['Id']);

        $this->docker->containers()->unpause($created['Id']);

        $info = $this->docker->containers()->inspect($created['Id']);
        expect($info['State']['Paused'])->toBeFalse()
            ->and($info['State']['Running'])->toBeTrue();
    });

    it('throws when unpausing a non-existent container', function () {
        expect(fn () => $this->docker->containers()->unpause('does-not-exist-'.uniqid()))
            ->toThrow(DockerException::class);
    });
});
