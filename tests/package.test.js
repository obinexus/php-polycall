'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const binding = require('..');
const metadata = require('../package.json');
const composer = require('../composer.json');

assert.equal(metadata.name, '@obinexusltd/php-polycall');
assert.equal(metadata.license, 'MIT');
assert.equal(metadata.publishConfig.access, 'public');
assert.equal(composer.name, 'obinexus/php-polycall');
assert.equal(composer.license, 'MIT');

const author = typeof metadata.author === 'string'
  ? metadata.author
  : `${metadata.author?.name} <${metadata.author?.email}>`;
assert.equal(author, 'Nnamdi Michael Okpala <okpalan@protonmail.com>');

const metadataKeys = {
  src: 'src',
  generated: 'generated',
  dist: 'dist',
  examples: 'example',
  tests: 'test',
  scripts: 'scripts'
};

for (const [name, directory] of Object.entries(binding.directories)) {
  const metadataKey = metadataKeys[name];
  assert.equal(metadata.directories[metadataKey], directory.relative);
  assert.equal(path.isAbsolute(metadata.directories[metadataKey]), false);
  assert.equal(fs.statSync(directory.root).isDirectory(), true);
  assert.ok(directory.files.length > 0, `${name} directory index is empty`);
  assert.ok(directory.files.every((file) => file.startsWith(`${directory.root}${path.sep}`)));
}

assert.ok(binding.directories.src.relativeFiles.includes('Polycall.php'));
assert.ok(binding.directories.src.relativeFiles.includes('PolycallException.php'));
assert.ok(binding.directories.generated.relativeFiles.includes('polycall/polycall_ffi.h'));
assert.ok(binding.directories.examples.relativeFiles.includes('run.php'));
assert.throws(() => binding.resolve('src', '..', 'package.json'), RangeError);

for (const file of [
  binding.phpClass,
  binding.phpException,
  binding.ffiHeader,
  binding.composer,
  binding.config,
  binding.manifest
]) {
  assert.equal(path.isAbsolute(file), true, `path is not absolute: ${file}`);
  assert.equal(fs.existsSync(file), true, `missing project file: ${file}`);
}

console.log('php-polycall npm relative-directory index test: PASS');
