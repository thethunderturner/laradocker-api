<?php

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
