<?php

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
    $marker = 'limit-test-'.uniqid();

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
    $marker = 'filter-test-'.uniqid();

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
