<?php

it('returns events within a time window', function () {
    $now = time();

    $events = $this->docker->system()->events(
        since: $now - 60,
        until: $now,
    );

    expect($events)->toBeArray();

    foreach ($events as $event) {
        expect($event)->toHaveKey('Type')
            ->and($event)->toHaveKey('Action');
    }
});

it('filters events by container type only', function () {
    $now = time();

    $events = $this->docker->system()->events(
        since: $now - 60,
        until: $now,
        filters: ['type' => ['container']],
    );

    expect($events)->toBeArray();

    foreach ($events as $event) {
        expect($event['Type'])->toBe('container');
    }
});
