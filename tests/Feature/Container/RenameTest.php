<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

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
