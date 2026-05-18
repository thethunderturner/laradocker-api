<?php

use TheThunderTurner\Docker\Docker;
use TheThunderTurner\Docker\Exceptions\DockerException;

beforeEach(function (): void {
    $this->docker = new Docker;
    $this->cleanup = [];
});

afterEach(function () {
    foreach ($this->cleanup as $id) {
        try {
            $this->docker->containers()->remove($id, force: true);
        } catch (Throwable) {
        }
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

it('lists processes running inside a container', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start($created['Id']);

    $top = $this->docker->containers()->top($created['Id']);

    expect($top)->toHaveKey('Titles')
        ->and($top)->toHaveKey('Processes')
        ->and($top['Titles'])->toBeArray()
        ->and($top['Processes'])->toBeArray()
        ->and($top['Processes'])->not->toBeEmpty();

    $allProcessesText = implode(' ', array_map(
        fn ($row) => implode(' ', $row),
        $top['Processes']
    ));
    expect($allProcessesText)->toContain('sleep');
});

it('top accepts custom ps args', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
    ]);
    $this->cleanup[] = $created['Id'];
    $this->docker->containers()->start(id: $created['Id']);

    $top = $this->docker->containers()->top($created['Id'], psArgs: 'aux');

    $titles = $top['Titles'];
    expect($titles)->toContain('USER');
});

it('top throws when container is not running', function () {
    $created = $this->docker->containers()->create([
        'Image' => 'alpine:latest',
        'Cmd' => ['sleep', '30'],
    ]);
    $this->cleanup[] = $created['Id'];

    // Container created but never started — top should fail (409 Conflict)
    expect(fn () => $this->docker->containers()->top($created['Id']))
        ->toThrow(DockerException::class);
});

it('top throws for non-existent container', function () {
    expect(fn () => $this->docker->containers()->top('does-not-exist-' . uniqid()))
        ->toThrow(DockerException::class);
});

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
