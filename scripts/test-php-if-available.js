'use strict';

const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');

const root = path.resolve(__dirname, '..');
const required = process.argv.includes('--required');
const phpProbe = spawnSync('php', ['--version'], { stdio: 'ignore' });

if (phpProbe.error || phpProbe.status !== 0) {
  if (required) {
    console.error('PHP is required for this command but was not found on PATH');
    process.exit(1);
  }
  console.log('PHP not found; skipping PHP FFI smoke test');
  process.exit(0);
}

const ffiProbe = spawnSync(
  'php',
  [
    '-d',
    'ffi.enable=1',
    '-r',
    'exit(extension_loaded("ffi") && class_exists("FFI", false) ? 0 : 2);'
  ],
  { stdio: 'ignore' }
);

if (ffiProbe.error || ffiProbe.status !== 0) {
  if (required) {
    console.error(
      'PHP ext-ffi is required for this command but is not loaded; enable the FFI extension in php.ini'
    );
    process.exit(1);
  }
  console.log('PHP ext-ffi is not loaded; skipping PHP FFI smoke test');
  process.exit(0);
}

const buildDirectory = path.join(root, 'build');
fs.mkdirSync(buildDirectory, { recursive: true });

let libraryName;
let compilerArguments;
if (process.platform === 'win32') {
  libraryName = 'polycall_ffi_mock.dll';
  compilerArguments = ['-std=c11', '-Wall', '-Wextra', '-Wpedantic', '-shared'];
} else if (process.platform === 'darwin') {
  libraryName = 'libpolycall_ffi_mock.dylib';
  compilerArguments = ['-std=c11', '-Wall', '-Wextra', '-Wpedantic', '-dynamiclib'];
} else {
  libraryName = 'libpolycall_ffi_mock.so';
  compilerArguments = ['-std=c11', '-Wall', '-Wextra', '-Wpedantic', '-shared', '-fPIC'];
}

const libraryPath = path.join(buildDirectory, libraryName);
compilerArguments.push(
  path.join('tests', 'polycall_ffi_mock.c'),
  '-o',
  libraryPath
);

const compile = spawnSync('gcc', compilerArguments, {
  cwd: root,
  stdio: 'inherit'
});
if (compile.error || compile.status !== 0) {
  console.error('failed to compile the PHP FFI mock library');
  process.exit(compile.status || 1);
}

const test = spawnSync(
  'php',
  ['-d', 'ffi.enable=1', path.join('tests', 'php.test.php')],
  {
    cwd: root,
    env: { ...process.env, POLYCALL_LIBRARY: libraryPath },
    stdio: 'inherit'
  }
);

if (test.error || test.status !== 0) {
  process.exit(test.status || 1);
}
