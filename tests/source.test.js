'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const sourcePath = path.resolve(__dirname, '..', 'src', 'Polycall.php');
const source = fs.readFileSync(sourcePath, 'utf8');

assert.match(
  source,
  /int polycall_ffi_run_config\(const char \*config_path, int validate\);/
);
assert.match(source, /polycall_ffi_run_config\(\$path, 1\)/);
assert.match(source, /throw new PolycallException/);
assert.doesNotMatch(source, /polycall_ffi_(?:version|describe)/);
assert.doesNotMatch(
  source,
  /\b(?:parse_ini_file|file_get_contents|fopen|socket_create)\s*\(/
);

console.log('php-polycall thin FFI source test: PASS');
