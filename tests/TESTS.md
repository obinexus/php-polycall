# Tests

- `package.test.js` validates public npm metadata, Composer metadata, author,
  license, exports, and every indexed project-relative directory.
- `source.test.js` enforces the one-call FFI boundary and rejects embedded
  configuration parsing or unrelated core APIs.
- `php.test.php` tests raw statuses and `PolycallException` against a generated
  mock shared library when PHP with `ext-ffi` is installed.
