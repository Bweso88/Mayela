<?php

use App\Modules\Identity\Support\StudentLoginCodeGenerator;

it('génère des codes lisibles sans caractères ambigus', function () {
    $code = app(StudentLoginCodeGenerator::class)->generate();

    expect($code)->toMatch('/^MY-[2-9A-HJKMNP-Z]{6}$/');
});

it('normalise la saisie de l\'utilisateur', function (string $input) {
    expect(StudentLoginCodeGenerator::normalize($input))->toBe('MY-7KQ4PX');
})->with(['MY-7KQ4PX', 'my7kq4px', ' my-7kq4px ', 'MY 7KQ 4PX']);
