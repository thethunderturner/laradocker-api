<?php

it('creates a container with minimal config', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
    ]);
    $this->cleanup[] = $created['Id'];

    expect($created['Id'])->toBeString();
});

it('accepts a platform', function () {
    $created = $this->docker->containers()->create(
        ['Image' => 'alpine:latest'],
        platform: 'linux/amd64',
    );
    $this->cleanup[] = $created['Id'];

    expect($created['Id'])->toBeString();
});

it('accepts a name', function () {
    $name = 'pest-'.uniqid();
    $created = $this->docker->containers()->create(
        ['Image' => 'alpine:latest'],
        name: $name,
    );
    $this->cleanup[] = $created['Id'];

    $info = $this->docker->containers()->inspect($created['Id']);
    expect($info['Name'])->toBe("/{$name}");
});
