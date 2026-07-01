# php-polycall

**PHP** binding for [libpolycall](https://github.com/obinexus/libpolycall) — an
implemented reference adapter.

A thin adapter over the flat FFI boundary (`polycall_ffi.h`). It contains no
config or runtime logic; every call forwards to the shared C core. See
[../../docs/adapter-pattern.md](../../docs/adapter-pattern.md).

## Build & run

```bash
cd ../.. && ./setup.sh          # build the shared core (build/libpolycall.*)
cd bindings/php-polycall
php -d ffi.enable=1 src/Polycall.php php-polycallrc
```

Requires PHP >= 7.4 with the built-in `ext-ffi` enabled.

## Config

Read-only config: [`php-polycallrc`](php-polycallrc) — the standard `*polycallrc` convention on
the single shared schema. No per-language parser exists.

## Manifest

See [`polycall-binding.json`](polycall-binding.json).
