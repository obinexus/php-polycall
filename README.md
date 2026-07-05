# @obinexusltd/php-polycall

PHP FFI source binding for
[libpolycall](https://github.com/obinexus/libpolycall) 1.5, packaged for public
distribution through npmjs.org.

The adapter is intentionally thin: it declares only
`polycall_ffi_run_config`, passes a configuration path with validation mode
`1`, and returns the core status unchanged. Configuration parsing and runtime
behavior remain in libpolycall.

## Install from npm

```powershell
npm install @obinexusltd/php-polycall
```

The npm package contains portable PHP source, Composer metadata, the generated
FFI declaration, examples, tests, and runtime configuration. It does not bundle
a platform-specific libpolycall binary.

## Requirements

- PHP 7.4 or newer
- PHP's built-in `ext-ffi`
- A libpolycall shared library exporting `polycall_ffi_run_config`

Enable FFI for CLI use when necessary:

```powershell
php -d ffi.enable=1 examples/run.php php-polycallrc
```

Set the library path explicitly for reliable deployment:

```powershell
$env:POLYCALL_LIBRARY = 'C:\path\to\libpolycall.dll'
```

Without `POLYCALL_LIBRARY`, the adapter searches the package and parent
workspace `build`, `bin`, and `lib` directories for standard library names.

## PHP API

```php
<?php

use OBINexus\Polycall\Polycall;
use OBINexus\Polycall\PolycallException;

require_once __DIR__ . '/node_modules/@obinexusltd/php-polycall/src/Polycall.php';

$polycall = new Polycall();

$status = $polycall->runConfig('php-polycallrc');
if ($status !== 0) {
    exit($status);
}

try {
    $polycall->runConfigOrThrow('php-polycallrc');
} catch (PolycallException $error) {
    echo $error->status();
}
```

- `runConfig(?string $path): int` returns the core status unchanged.
- `runConfigOrThrow(?string $path): void` raises `PolycallException` for a
  nonzero status.
- Omitting the path uses this package's `php-polycallrc`.
- Passing a library path to `new Polycall($path)` overrides discovery.

## Composer compatibility

The included [`composer.json`](composer.json) provides PSR-4 autoloading:

```powershell
composer install
```

The Composer package identity is `obinexus/php-polycall`; npm remains the
distribution target requested here.

## Test

```powershell
npm test
```

Tests validate npm/Composer metadata, relative directory indexing, and the
one-call FFI boundary. If PHP is installed, the suite compiles a tiny mock
shared library with GCC and exercises the real PHP FFI path automatically.

PHP is not installed on the current machine, so that optional smoke test is
reported as skipped while the mandatory publication tests still run. Once PHP
is available, require the language test explicitly with:

```powershell
npm run test:php
```

## JavaScript build-tool entry point

```js
const binding = require('@obinexusltd/php-polycall');

console.log(binding.phpClass);
console.log(binding.directories.src.relativeFiles);
console.log(binding.resolve('examples', 'run.php'));
```

All exported directory paths are project-relative in `package.json` and become
absolute only through the CommonJS entry point. `resolve()` prevents traversal
outside the selected directory.

## Publish publicly

```powershell
npm pack --dry-run
npm publish --access public
```

`publishConfig.access` is already set to `public`. Publishing is not performed
automatically.

## Author and license

Nnamdi Michael Okpala (`okpalan@protonmail.com`). Licensed under MIT.
