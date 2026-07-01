<?php
/**
 * php-polycall - PHP reference adapter for libpolycall.
 *
 * A thin adapter over the flat FFI boundary (include/polycall/polycall_ffi.h)
 * using PHP's built-in FFI extension (PHP >= 7.4, ext-ffi enabled). No config
 * parsing or runtime logic lives here: every method forwards to the shared C
 * core.
 *
 * Usage:
 *   php -d ffi.enable=1 src/Polycall.php ../php-polycallrc
 */

declare(strict_types=1);

namespace OBINexus\Polycall;

final class PolycallException extends \RuntimeException
{
    public int $code;
    public function __construct(string $message, int $code)
    {
        parent::__construct("$message (status=$code)");
        $this->code = $code;
    }
}

final class Polycall
{
    public const VERSION = "1.5.0";

    private \FFI $ffi;

    public function __construct()
    {
        if (!\extension_loaded('ffi')) {
            throw new PolycallException("PHP ext-ffi is not enabled", 7);
        }
        $lib = self::locateLibrary();
        // Declare only the flat FFI boundary - nothing about core internals.
        $this->ffi = \FFI::cdef(
            "int polycall_ffi_version(char *buf, int buf_len);\n" .
            "int polycall_ffi_run_config(const char *path, int run);\n" .
            "int polycall_ffi_describe(const char *path, char *buf, int buf_len);\n",
            $lib
        );
    }

    private static function locateLibrary(): string
    {
        $root = \dirname(__DIR__, 3); // bindings/php-polycall/src -> repo root
        $names = ['libpolycall.dll', 'polycall.dll', 'libpolycall.so', 'libpolycall.dylib'];
        foreach (['build', 'bin'] as $d) {
            foreach ($names as $n) {
                $p = "$root/$d/$n";
                if (\is_file($p)) {
                    return $p;
                }
            }
        }
        throw new PolycallException("shared libpolycall not found; build the core first (./setup.sh)", 6);
    }

    public function version(): string
    {
        $buf = \FFI::new("char[64]");
        $this->ffi->polycall_ffi_version($buf, 64);
        return \FFI::string($buf);
    }

    public function runConfig(?string $path = null, bool $run = true): int
    {
        $rc = $this->ffi->polycall_ffi_run_config($path, $run ? 1 : 0);
        if ($rc !== 0) {
            throw new PolycallException("run_config(" . ($path ?? 'null') . ")", $rc);
        }
        return $rc;
    }

    public function describe(?string $path = null): string
    {
        $buf = \FFI::new("char[256]");
        $rc = $this->ffi->polycall_ffi_describe($path, $buf, 256);
        $text = \FFI::string($buf);
        if ($rc !== 0) {
            throw new PolycallException("describe: $text", $rc);
        }
        return $text;
    }
}

// Tiny CLI when run directly.
if (isset($argv) && \realpath($argv[0]) === \realpath(__FILE__)) {
    $cfg = $argv[1] ?? null;
    try {
        $pc = new Polycall();
        echo "php-polycall using libpolycall " . $pc->version() . "\n";
        echo $pc->describe($cfg) . "\n";
        exit($pc->runConfig($cfg));
    } catch (PolycallException $e) {
        \fwrite(\STDERR, "php-polycall error: " . $e->getMessage() . "\n");
        exit($e->code);
    }
}
