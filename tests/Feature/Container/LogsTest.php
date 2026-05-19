<?php

use TheThunderTurner\Docker\Exceptions\DockerException;

it('reads stdout from a container', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sh', '-c', 'echo hello-world'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);
    $this->docker->containers()->wait($created['Id']);

    $logs = $this->docker->containers()->logs($created['Id']);

    expect($logs)->toHaveKey('stdout')
        ->and($logs)->toHaveKey('stderr')
        ->and($logs['stdout'])->toContain('hello-world')
        ->and($logs['stderr'])->toBe('');
});

it('separates stdout and stderr', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sh', '-c', 'echo to-stdout; echo to-stderr 1>&2'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);
    $this->docker->containers()->wait($created['Id']);

    $logs = $this->docker->containers()->logs($created['Id']);

    expect($logs['stdout'])->toContain('to-stdout')
        ->and($logs['stdout'])->not->toContain('to-stderr')
        ->and($logs['stderr'])->toContain('to-stderr')
        ->and($logs['stderr'])->not->toContain('to-stdout');
});

it('can return only stdout when stderr is disabled', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sh', '-c', 'echo out; echo err 1>&2'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);
    $this->docker->containers()->wait($created['Id']);

    $logs = $this->docker->containers()->logs($created['Id'], stderr: false);

    expect($logs['stdout'])->toContain('out')
        ->and($logs['stderr'])->toBe('');
});

it('respects the tail parameter', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sh', '-c', 'for i in 1 2 3 4 5; do echo line-$i; done'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);
    $this->docker->containers()->wait($created['Id']);

    $logs = $this->docker->containers()->logs($created['Id'], tail: '2');

    $lines = array_values(array_filter(explode("\n", trim($logs['stdout']))));

    expect($lines)->toHaveLength(2)
        ->and($lines[0])->toBe('line-4')
        ->and($lines[1])->toBe('line-5');
});

it('includes timestamps when requested', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['echo', 'with-timestamp'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);
    $this->docker->containers()->wait($created['Id']);

    $logs = $this->docker->containers()->logs($created['Id'], timestamps: true);

    expect($logs['stdout'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
});

it('returns empty streams for a container that produced no output', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['true'],  // does nothing, exits 0
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);
    $this->docker->containers()->wait($created['Id']);

    $logs = $this->docker->containers()->logs($created['Id']);

    expect($logs['stdout'])->toBe('')
        ->and($logs['stderr'])->toBe('');
});

it('reads logs from a stopped container', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['echo', 'before-stop'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);
    $this->docker->containers()->wait($created['Id']);

    // Container has exited — logs should still be readable
    $logs = $this->docker->containers()->logs($created['Id']);

    expect($logs['stdout'])->toContain('before-stop');
});

it('logs throws for non-existent container', function () {
    expect(fn () => $this->docker->containers()->logs('does-not-exist-' . uniqid()))
        ->toThrow(DockerException::class);
});
