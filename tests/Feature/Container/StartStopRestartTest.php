<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

describe('start()', function () {
    it('starts a created container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '10'],
        ]);
        $this->cleanup[] = $created['Id'];

        $this->docker->containers()->start($created['Id']);

        $info = $this->docker->containers()->inspect($created['Id']);
        expect($info['State']['Running'])->toBeTrue();
    });

    it('throws when starting a non-existent container', function () {
        expect(fn () => $this->docker->containers()->start('does-not-exist-'.uniqid()))
            ->toThrow(DockerException::class);
    });
});

describe('stop()', function () {
    it('stops a running container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '30'],
        ]);
        $this->cleanup[] = $created['Id'];
        $this->docker->containers()->start($created['Id']);

        $this->docker->containers()->stop($created['Id']);

        $info = $this->docker->containers()->inspect($created['Id']);
        expect($info['State']['Running'])->toBeFalse();
    });

    it('stops a container with signal and timeout', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '30'],
        ]);
        $this->cleanup[] = $created['Id'];
        $this->docker->containers()->start($created['Id']);

        // Stop with SIGTERM and 5-second timeout — should still stop cleanly.
        $this->docker->containers()->stop($created['Id'], signal: 'SIGTERM', timeout: 5);

        $info = $this->docker->containers()->inspect($created['Id']);
        expect($info['State']['Running'])->toBeFalse();
    });

    it('throws when stopping a non-existent container', function () {
        expect(fn () => $this->docker->containers()->stop('does-not-exist-'.uniqid()))
            ->toThrow(DockerException::class);
    });
});

describe('restart()', function () {
    it('restarts a running container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '30'],
        ]);
        $this->cleanup[] = $created['Id'];
        $this->docker->containers()->start($created['Id']);

        $this->docker->containers()->restart($created['Id']);

        $info = $this->docker->containers()->inspect($created['Id']);
        expect($info['State']['Running'])->toBeTrue();
    });

    it('restarts a stopped container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '10'],
        ]);
        $this->cleanup[] = $created['Id'];

        // Restarting a stopped container should start it.
        $this->docker->containers()->restart($created['Id']);

        $info = $this->docker->containers()->inspect($created['Id']);
        expect($info['State']['Running'])->toBeTrue();
    });

    it('restarts with signal and timeout', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '30'],
        ]);
        $this->cleanup[] = $created['Id'];
        $this->docker->containers()->start($created['Id']);

        $this->docker->containers()->restart($created['Id'], signal: 'SIGTERM', timeout: 5);

        $info = $this->docker->containers()->inspect($created['Id']);
        expect($info['State']['Running'])->toBeTrue();
    });

    it('throws when restarting a non-existent container', function () {
        expect(fn () => $this->docker->containers()->restart('does-not-exist-'.uniqid()))
            ->toThrow(DockerException::class);
    });
});
