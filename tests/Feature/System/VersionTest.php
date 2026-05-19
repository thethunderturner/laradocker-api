<?php

it('returns the Docker engine version', function () {
    $version = $this->docker->system()->version();

    expect($version)->toHaveKey('Version')
        ->and($version)->toHaveKey('ApiVersion')
        ->and($version)->toHaveKey('Os')
        ->and($version)->toHaveKey('Arch');
});
