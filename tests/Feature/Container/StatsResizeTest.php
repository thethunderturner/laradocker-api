<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

describe('stats()', function () {
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
});

describe('resize()', function () {
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
});
