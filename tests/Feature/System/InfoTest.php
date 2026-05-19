<?php

it('returns system-wide information', function () {
    $info = $this->docker->system()->info();

    expect($info)->toHaveKey('ID')
        ->and($info)->toHaveKey('Containers')
        ->and($info)->toHaveKey('Images')
        ->and($info)->toHaveKey('Driver')
        ->and($info)->toHaveKey('OperatingSystem')
        ->and($info)->toHaveKey('Architecture')
        ->and($info)->toHaveKey('NCPU')
        ->and($info)->toHaveKey('MemTotal');
});
