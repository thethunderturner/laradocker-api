<?php

it('returns disk usage data', function () {
    $usage = $this->docker->system()->usage(type: ['container', 'image', 'volume'], verbose: true);

    expect($usage)->toHaveKey('ImageUsage')
        ->and($usage)->toHaveKey('ContainerUsage')
        ->and($usage)->toHaveKey('VolumeUsage')
        ->and($usage['ContainerUsage'])->toBeArray();
});

it('returns only images when image type is requested', function () {
    $usage = $this->docker->system()->usage(type: ['image'], verbose: false);

    expect($usage)->toHaveKey('ImageUsage');
});
