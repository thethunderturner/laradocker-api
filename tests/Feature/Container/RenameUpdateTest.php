<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

describe('rename()', function () {
    it('renames a container', function () {
        $oldName = 'rename-old-'.uniqid();
        $newName = 'rename-new-'.uniqid();

        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '5'],
        ], name: $oldName);
        $this->cleanup[] = $created['Id'];

        $this->docker->containers()->rename($created['Id'], $newName);

        $info = $this->docker->containers()->inspect($created['Id']);
        expect($info['Name'])->toBe("/{$newName}");
    });

    it('throws when renaming to an already-used name', function () {
        $name1 = 'rename-conflict-1-'.uniqid();
        $name2 = 'rename-conflict-2-'.uniqid();

        $c1 = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '5'],
        ], name: $name1);
        $this->cleanup[] = $c1['Id'];

        $c2 = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '5'],
        ], name: $name2);
        $this->cleanup[] = $c2['Id'];

        expect(fn () => $this->docker->containers()->rename($c2['Id'], $name1))
            ->toThrow(DockerException::class);
    });

    it('throws when renaming a non-existent container', function () {
        expect(fn () => $this->docker->containers()->rename('does-not-exist-'.uniqid(), 'new-name'))
            ->toThrow(DockerException::class);
    });
});

describe('update()', function () {
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
});
