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

const globOptions = {
  // Search within the ckeditor npm namespace.
  cwd: process.cwd() + '/node_modules/@ckeditor/',
  absolute: true,
};

/**
 * The list of files where type data can't be extracted.
 *
 * @type {array}
 */
const ignored = [];

/**
 * Template for the generated typedef comment.
 *
 * @param {string} file
 *  The path to the file containing the type definition.
 * @param {string} module
 *  The module name as defined by the @module jsdoc comment.
 * @param {string} name
 *  The name of the class being exported
 *
 * @return {string}
 *  The comment aliasing the module name to the specific named exports.
 */
function generateTypeDef(file, module, name) {
  const cleanModule = module.replace('module:', '');
  return `/**
 * Declared in file @ckeditor/${file.replace(globOptions.cwd, '')}
 *
 * @typedef {module:${cleanModule}} module:${cleanModule}~${name}
 */
`;
}


/**
 * Helper to get the file contents as a string.
 *
 * @param {string} filePath
 *  Absolute path to the file.
 *
 * @return {string}
 */
function getFile(filePath) {
  try {
    return fs.readFileSync(filePath, 'utf8');
  } catch (err) {
    return '';
  }
}

/**
 * Returns a callback function.
 *
 * @param {RegExp} regex
 *  The regex used to find exports to alias.
 *
 * @return {function}
 *  The callback function applied to each file found. It applies the regex
 *  to the file contents and returns a typedef string.
 *
 * @see generateTypeDef
 */
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
    /**
     * Makes sure that `export default class` code can be referenced with the
     * class name and not the module name only.
     */
    regex: / * @module \b(.*)\b[\s\S]*?export default class \b(\w+)\b/g,
  },
  jsdoc: {
    search: './ckeditor5*/src/**/*.jsdoc',
    /**
     * Pick up ckeditor own aliases to alias them too.
     */
    regex: / * @module \b(.*)\b[\s\S]*?@typedef .* .*~(\w+)/g,
  }
}


const definitions = [
  ...glob.sync(match.js.search, globOptions).map(processFile(match.js.regex)),
  ...glob.sync(match.jsdoc.search, globOptions).map(processFile(match.jsdoc.regex)),
];
// Filter definitions that do not match any regex.
const existingDefinitions = definitions.filter((e) => !!e);

// Write the file in the ckeditor module, use the jsdoc extension to make sure
// the jsdoc extension is associated with the javascript file type and it
// prevents core JS lint rules to be run. Add it to the build folder to prevent
// cspell checks on this file.
fs.writeFile(`./modules/ckeditor5/js/build/ckeditor5.types.jsdoc`, existingDefinitions.join('\n'), () => {
  log(`CKEditor5 types have been generated: ${existingDefinitions.length} declarations found, ${definitions.length - existingDefinitions.length} files ignored`);
});

process.exitCode = 0;
