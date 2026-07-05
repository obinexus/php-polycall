<?php

declare(strict_types=1);

use OBINexus\Polycall\Polycall;
use OBINexus\Polycall\PolycallException;

require_once \dirname(__DIR__) . '/src/Polycall.php';

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$polycall = new Polycall();
expect($polycall->runConfig('explicit-polycallrc') === 0, 'explicit path failed');
expect($polycall->runConfig() === 0, 'default path failed');
expect(
    $polycall->runConfig('__status_37__') === 37,
    'raw status was not preserved'
);

$caught = false;
try {
    $polycall->runConfigOrThrow('__status_37__');
} catch (PolycallException $error) {
    expect($error->status() === 37, 'exception status was not preserved');
    expect($error->getCode() === 37, 'exception code was not preserved');
    $caught = true;
}

expect($caught, 'expected PolycallException was not raised');
echo "php-polycall PHP FFI smoke test: PASS\n";
