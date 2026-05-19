<?php

describe('prune()', function () {
    it('removes all stopped containers', function () {
        // Create a short-lived container that exits.
        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['echo', 'done'],
        ]);
        $this->docker->containers()->start($created['Id']);
        $this->docker->containers()->wait($created['Id']);

        $result = $this->docker->containers()->prune();

        expect($result)->toHaveKey('ContainersDeleted')
            ->and($result)->toHaveKey('SpaceReclaimed');
    });

    it('prunes with filters', function () {
        $label = 'prune-filter='.uniqid();

        $created = $this->docker->containers()->create([
            'Image' => 'alpine:latest',
            'Cmd' => ['echo', 'done'],
            'Labels' => ['prune-filter' => 'yes'],
        ]);
        $this->docker->containers()->start($created['Id']);
        $this->docker->containers()->wait($created['Id']);

        $result = $this->docker->containers()->prune(filters: ['label' => [$label]]);
        expect($result)->toHaveKey('ContainersDeleted');
    });
});
