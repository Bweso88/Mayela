<?php

it('liste publiquement les classes proposées, dans l\'ordre, avec leurs séries', function () {
    $response = $this->getJson('/api/v1/catalog/cg/grades')->assertOk();

    expect(collect($response->json('data'))->pluck('code')->all())
        ->toBe(['CE1', 'CE2', 'CM1', 'CM2', '6E', '5E', '4E', '3E', '2NDE', '1ERE', 'TLE']);

    $premiere = collect($response->json('data'))->firstWhere('code', '1ERE');
    expect($premiere['universe'])->toBe('lycee')
        ->and($premiere['cycle']['name'])->toBe('Lycée')
        ->and(collect($premiere['tracks'])->pluck('code')->all())->toBe(['A', 'B', 'C', 'D']);
});

it('renvoie 404 pour un pays inconnu', function () {
    $this->getJson('/api/v1/catalog/zz/grades')->assertNotFound();
});
