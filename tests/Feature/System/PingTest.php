<?php

it('returns OK from the Docker daemon', function () {
    $result = $this->docker->system()->ping();

    expect($result)->toBeString()
        ->and(strtoupper($result))->toBe('OK');
});
