<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Imports\EmployeePegawaiImport;

$import = new EmployeePegawaiImport;
$ref = new ReflectionClass($import);

$date = $ref->getMethod('date');
$date->setAccessible(true);
$gender = $ref->getMethod('normalizeGender');
$gender->setAccessible(true);

assert($date->invoke($import, '15-08-1990') === '1990-08-15', 'dd-mm-yyyy gagal');
assert($date->invoke($import, '1990-08-15') === '1990-08-15', 'ISO gagal');
assert($date->invoke($import, '') === null, 'empty harus null');
assert($date->invoke($import, '31-02-2020') === null, 'invalid harus null');

assert($gender->invoke($import, 'L') === 'L');
assert($gender->invoke($import, 'perempuan') === 'P');
assert($gender->invoke($import, 'x') === null);

assert(EmployeePegawaiImport::headers()[0] === 'status');
assert(count(EmployeePegawaiImport::headers()) === 30);

echo "OK: EmployeePegawaiImport self-check passed\n";
