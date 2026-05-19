<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

describe('wait()', function () {
    it('waits for a container to exit and returns the exit code', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sh', '-c', 'exit 42'],
        ]);
        $this->cleanup[] = $created['Id'];
        $this->docker->containers()->start($created['Id']);

        $result = $this->docker->containers()->wait($created['Id']);

        expect($result)->toHaveKey('StatusCode')
            ->and($result['StatusCode'])->toBe(42);
    });

    it('returns the correct exit code', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sh', '-c', 'exit 7'],
        ]);
        $this->cleanup[] = $created['Id'];
        $this->docker->containers()->start($created['Id']);

        $result = $this->docker->containers()->wait($created['Id'], condition: 'not-running');

        expect($result)->toHaveKey('StatusCode')
            ->and($result['StatusCode'])->toBe(7);
    });

    it('throws when waiting on a non-existent container', function () {
        expect(fn () => $this->docker->containers()->wait('does-not-exist-'.uniqid()))
            ->toThrow(DockerException::class);
    });
});

describe('remove()', function () {
    it('removes a stopped container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['echo', 'done'],
        ]);
        $this->cleanup[] = $created['Id'];

        $this->docker->containers()->remove($created['Id']);

        // Remove from cleanup so afterEach doesn't try again.
        $this->cleanup = [];

        expect(fn () => $this->docker->containers()->inspect($created['Id']))
            ->toThrow(DockerException::class);
    });

    it('force removes a running container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '30'],
        ]);
        $this->cleanup[] = $created['Id'];
        $this->docker->containers()->start($created['Id']);

        $this->docker->containers()->remove($created['Id'], force: true);
        $this->cleanup = [];

        expect(fn () => $this->docker->containers()->inspect($created['Id']))
            ->toThrow(DockerException::class);
    });

    it('throws when removing a non-existent container', function () {
        expect(fn () => $this->docker->containers()->remove('does-not-exist-'.uniqid()))
            ->toThrow(DockerException::class);
    });
});
