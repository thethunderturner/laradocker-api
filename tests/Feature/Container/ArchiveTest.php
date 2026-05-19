<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

describe('archiveInfo()', function () {
    it('returns filesystem header information about a path', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '10'],
        ]);
        $this->cleanup[] = $created['Id'];

        $info = $this->docker->containers()->archiveInfo($created['Id'], '/etc/hostname');

        expect($info)->toHaveKey('name')
            ->and($info)->toHaveKey('size')
            ->and($info)->toHaveKey('mode');
    });

    it('throws for a non-existent container', function () {
        expect(fn () => $this->docker->containers()->archiveInfo('does-not-exist-'.uniqid(), '/etc/hostname'))
            ->toThrow(DockerException::class);
    });
});

describe('getArchive()', function () {
    it('retrieves a tar archive of a path from a container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '10'],
        ]);
        $this->cleanup[] = $created['Id'];

        $tarball = $this->docker->containers()->getArchive($created['Id'], '/etc/hostname');

        // A valid tar entry must start with the filename at offset 0.
        expect($tarball)->toBeString()
            ->and(strlen($tarball))->toBeGreaterThan(512);
    });

    it('throws for a non-existent container', function () {
        expect(fn () => $this->docker->containers()->getArchive('does-not-exist-'.uniqid(), '/etc/hostname'))
            ->toThrow(DockerException::class);
    });
});

describe('putArchive()', function () {
    it('uploads and extracts a tar archive into a container', function () {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '10'],
        ]);
        $this->cleanup[] = $created['Id'];

        $content = 'hello-from-archive';
        $tarball = buildTar('test.txt', $content);

        $this->docker->containers()->putArchive($created['Id'], '/tmp', $tarball);

        $archiveInfo = $this->docker->containers()->archiveInfo($created['Id'], '/tmp/test.txt');
        expect($archiveInfo['name'])->toBe('test.txt')
            ->and($archiveInfo['size'])->toBe(strlen($content));
    });

    it('throws for a non-existent container', function () {
        $tarball = str_repeat("\x00", 2048);

        expect(fn () => $this->docker->containers()->putArchive('does-not-exist-'.uniqid(), '/tmp', $tarball))
            ->toThrow(DockerException::class);
    });
});
