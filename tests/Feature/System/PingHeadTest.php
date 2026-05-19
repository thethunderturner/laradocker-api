<?php

it('returns headers without a response body', function () {
    $headers = $this->docker->system()->pingHead();

    expect($headers)->toBeArray()
        ->and($headers)->toHaveKey('Api-Version');
});
