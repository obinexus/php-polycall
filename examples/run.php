<?php

declare(strict_types=1);

use OBINexus\Polycall\Polycall;
use OBINexus\Polycall\PolycallException;

require_once \dirname(__DIR__) . '/src/Polycall.php';

$configPath = $argv[1] ?? null;

try {
    $polycall = new Polycall();
    $polycall->runConfigOrThrow($configPath);
    echo "libpolycall completed successfully\n";
} catch (PolycallException $error) {
    \fwrite(\STDERR, $error->getMessage() . "\n");
    exit($error->status());
}
