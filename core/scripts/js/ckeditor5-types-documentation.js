/**
 * @file
 *
 * Provides the build:ckeditor5-types command to generate ckeditor 5 types documentation.
 *
 * @internal This file is part of the core javascript build process and is only
 * meant to be used in that context.
 */

'use strict';

const glob = require('glob');
const log = require('./log');
const fs = require('fs');

// Ignore everything in node_modules
const globOptions = {
  cwd: process.cwd() + '/node_modules/@ckeditor/',
  absolute: true,
};

const ignored = [];

function generateTypeDef(file, module, name) {
  const cleanModule = module.replace('module:', '');
  return `/**
 * Declared in file @ckeditor/${file.replace(globOptions.cwd, '')}
 *
 * @typedef {module:${cleanModule}} module:${cleanModule}~${name}
 */
`;
}

function getFile(filePath) {
  try {
    return fs.readFileSync(filePath, 'utf8');
  } catch (err) {
    return '';
  }
}

function processFile(regex) {
  return (filePath) => {
    regex.lastIndex = 0;
    const m = regex.exec(getFile(filePath));
    if (m) {
      return generateTypeDef(filePath, m[1], m[2]);
    }
    return false;
  }
}

const match = {
  js: {
    search: './ckeditor5*/src/**/*.js',
    regex: / * @module \b(.*)\b[\s\S]*?export default class \b(\w+)\b/g,
  },
  jsdoc: {
    search: './ckeditor5*/src/**/*.jsdoc',
    regex: / * @module \b(.*)\b[\s\S]*?@typedef .* .*~(\w+)/g,
  }
}


const definitions = [
  ...glob.sync(match.js.search, globOptions).map(processFile(match.js.regex)),
  ...glob.sync(match.jsdoc.search, globOptions).map(processFile(match.jsdoc.regex)),
];
const existingDefinitions = definitions.filter(e => e);

const total = definitions.length;
const totalExisting = existingDefinitions.length;

fs.writeFile(`./modules/ckeditor5/js/ckeditor5.types.jsdoc`, definitions.join('\n\n'), () => {
  log(`CKEditor5 types have been generated: ${totalExisting} declarations found, ${total - totalExisting} files ignored`);
});

process.exitCode = 0;
