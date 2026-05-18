<?php

use TheThunderTurner\Docker\Docker;

beforeEach(function (): void {
    $this->docker = new Docker;
    $this->cleanup = [];
});

afterEach(function () {
    foreach ($this->cleanup as $id) {
        try {
            $this->docker->containers()->remove($id, force: true);
        } catch (\Throwable) {}
    }
});

it('create container', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sh', '-c', 'echo 0; sleep 60'],
    ]);
    $this->cleanup[] = $created['Id'];

    expect($created['Id'])->toBeString()->toHaveLength(64);
});

it('list containers', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
    ]);
    $this->cleanup[] = $created['Id'];

    $list = $this->docker->containers()->list(all: true);
    $ids = array_column($list, 'Id');

    expect($ids)->toContain($created['Id']);
});

it('list containers (with limit)', function () {
    $marker = 'limit-test-' . uniqid();

    for ($i = 1; $i <= 5; $i++) {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '30'],
            'Labels' => ['test-marker' => $marker],
        ]);
        $this->cleanup[] = $created['Id'];
    }

    $list = $this->docker->containers()->list(
        all: true,
        limit: 3,
        filters: ['label' => ["test-marker={$marker}"]],
    );

    expect($list)->toHaveLength(3);
});

it('list containers (with filter)', function () {
    $marker = 'filter-test-' . uniqid();

    foreach (['alice', 'bob', 'carol'] as $owner) {
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['sleep', '30'],
            'Labels' => [
                'test-marker' => $marker,
                'owner' => $owner,
            ],
        ]);
        $this->cleanup[] = $created['Id'];
    }

    $other = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
    ]);
    $this->cleanup[] = $other['Id'];

    $filtered = $this->docker->containers()->list(
        all: true,
        filters: ['label' => ["test-marker={$marker}"]],
    );
    expect($filtered)->toHaveLength(3);

    $alice = $this->docker->containers()->list(
        all: true,
        filters: ['label' => ["test-marker={$marker}", 'owner=alice']],
    );
    expect($alice)->toHaveLength(1);
});

it('inspect a container', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
        'Env' => ['FOO=bar'],
        'Labels' => ['test' => 'inspect'],
    ]);
    $this->cleanup[] = $created['Id'];

    $info = $this->docker->containers()->inspect($created['Id']);

    expect($info['Id'])->toBe($created['Id'])
        ->and($info['Config']['Image'])->toBe('alpine:latest')
        ->and($info['Config']['Cmd'])->toBe(['sleep', '30'])
        ->and($info['Config']['Labels']['test'])->toBe('inspect')
        ->and($info['Config']['Env'])->toContain('FOO=bar')
        ->and($info['State']['Status'])->toBe('created');
});
