<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

describe('changes()', function () {
    it('detects file changes in a container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sh', '-c', 'echo hello > /tmp/new-file'],
        ]);
        $this->cleanup[] = $created['Id'];
        $this->docker->containers()->start($created['Id']);
        $this->docker->containers()->wait($created['Id']);

        $changes = $this->docker->containers()->changes($created['Id']);

        expect($changes)->toBeArray();

        $paths = array_column($changes, 'Path');
        expect($paths)->toContain('/tmp')
            ->and($paths)->toContain('/tmp/new-file');
    });

    it('throws for a non-existent container', function () {
        expect(fn () => $this->docker->containers()->changes('does-not-exist-'.uniqid()))
            ->toThrow(DockerException::class);
    });
});

describe('export()', function () {
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
});
