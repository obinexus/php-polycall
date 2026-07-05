<?php

declare(strict_types=1);

namespace OBINexus\Polycall;

require_once __DIR__ . '/PolycallException.php';

final class Polycall
{
    public const VERSION = '1.0.0';
    public const DEFAULT_CONFIG = 'php-polycallrc';

    private const C_DEFINITION = <<<'CDEF'
int polycall_ffi_run_config(const char *config_path, int validate);
CDEF;

    private \FFI $ffi;

    public function __construct(?string $libraryPath = null)
    {
        if (!\extension_loaded('ffi')) {
            throw new PolycallException('PHP ext-ffi is not enabled', 7);
        }

        $resolvedPath = $libraryPath ?? self::locateLibrary();
        if (!\is_file($resolvedPath)) {
            throw new PolycallException(
                "libpolycall library does not exist: {$resolvedPath}",
                6
            );
        }

        try {
            $this->ffi = \FFI::cdef(self::C_DEFINITION, $resolvedPath);
        } catch (\Throwable $error) {
            throw new PolycallException(
                "unable to load libpolycall: {$resolvedPath}",
                6,
                $error
            );
        }
    }

    public static function locateLibrary(): string
    {
        $configured = \getenv('POLYCALL_LIBRARY');
        if (\is_string($configured) && $configured !== '') {
            if (!\is_file($configured)) {
                throw new PolycallException(
                    "POLYCALL_LIBRARY does not identify a file: {$configured}",
                    6
                );
            }
            return $configured;
        }

        $packageRoot = \dirname(__DIR__);
        $workspaceRoot = \dirname($packageRoot);
        $names = [
            'libpolycall.dll',
            'polycall.dll',
            'libpolycall.so',
            'libpolycall.dylib',
        ];

        foreach ([$packageRoot, $workspaceRoot] as $root) {
            foreach (['build', 'bin', 'lib'] as $directory) {
                foreach ($names as $name) {
                    $candidate = $root . \DIRECTORY_SEPARATOR . $directory
                        . \DIRECTORY_SEPARATOR . $name;
                    if (\is_file($candidate)) {
                        return $candidate;
                    }
                }
            }
        }

        throw new PolycallException(
            'shared libpolycall not found; set POLYCALL_LIBRARY to its absolute path',
            6
        );
    }

    public function runConfig(?string $configPath = null): int
    {
        $path = $configPath ?? \dirname(__DIR__) . \DIRECTORY_SEPARATOR
            . self::DEFAULT_CONFIG;

        return $this->ffi->polycall_ffi_run_config($path, 1);
    }

    public function runConfigOrThrow(?string $configPath = null): void
    {
        $status = $this->runConfig($configPath);
        if ($status !== 0) {
            throw new PolycallException(
                "libpolycall failed for configuration: "
                    . ($configPath ?? self::DEFAULT_CONFIG),
                $status
            );
        }
    }
}
