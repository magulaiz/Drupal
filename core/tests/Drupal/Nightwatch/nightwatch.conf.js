// cspell:ignore testcases
const path = require('path');
const { globSync } = require('glob');

// Find directories which have Nightwatch tests in them.
const regex = /(.*\/?tests\/?.*\/Nightwatch)\/.*/g;
const collectedFolders = {
  Tests: [],
  Commands: [],
  Assertions: [],
  Pages: [],
};
const searchDirectory = process.env.DRUPAL_NIGHTWATCH_SEARCH_DIRECTORY || '';
const defaultIgnore = ['vendor/**'];

globSync('**/tests/**/Nightwatch/**/*.js', {
  cwd: path.resolve(process.cwd(), `../${searchDirectory}`),
  ignore: process.env.DRUPAL_NIGHTWATCH_IGNORE_DIRECTORIES
    ? process.env.DRUPAL_NIGHTWATCH_IGNORE_DIRECTORIES.split(',').concat(
        defaultIgnore,
      )
    : defaultIgnore,
})
  .sort()
  .forEach((file) => {
    let m = regex.exec(file);
    while (m !== null) {
      // This is necessary to avoid infinite loops with zero-width matches.
      if (m.index === regex.lastIndex) {
        regex.lastIndex += 1;
      }

      const key = `../${m[1]}`;
      Object.keys(collectedFolders).forEach((folder) => {
        if (file.includes(`Nightwatch/${folder}`)) {
          collectedFolders[folder].push(`${searchDirectory}${key}/${folder}`);
        }
      });
      m = regex.exec(file);
    }
  });

// Remove duplicate folders.
Object.keys(collectedFolders).forEach((folder) => {
  collectedFolders[folder] = Array.from(new Set(collectedFolders[folder]));
});

let chromeArgs = [
  '--no-sandbox',
  '--ignore-certificate-errors',
  '--allow-insecure-localhost',
];
if (
  process.env.DRUPAL_TEST_WEBDRIVER_CHROME_ARGS &&
  process.env.DRUPAL_TEST_WEBDRIVER_CHROME_ARGS.length
) {
  chromeArgs = process.env.DRUPAL_TEST_WEBDRIVER_CHROME_ARGS.split(' ');
}

module.exports = {
  src_folders: collectedFolders.Tests,
  output_folder: process.env.DRUPAL_NIGHTWATCH_OUTPUT,
  custom_commands_path: collectedFolders.Commands,
  custom_assertions_path: collectedFolders.Assertions,
  page_objects_path: collectedFolders.Pages,
  globals_path: 'globals.js',
  webdriver: {
    start_process:
      process.env.DRUPAL_TEST_CHROMEDRIVER_AUTOSTART.toLowerCase() === 'true',
    host: process.env.DRUPAL_TEST_WEBDRIVER_HOSTNAME,
    port: parseInt(process.env.DRUPAL_TEST_WEBDRIVER_PORT, 10),
    log_path: process.env.DRUPAL_NIGHTWATCH_OUTPUT,
  },
  test_settings: {
    default: {
      globals: {
        defaultTheme: 'olivero',
        adminTheme: 'claro',
      },
      desiredCapabilities: {
        browserName: 'chrome',
        'goog:chromeOptions': {
          args: chromeArgs,
        },
      },
      default_path_prefix: process.env.DRUPAL_TEST_WEBDRIVER_PATH_PREFIX || '',
      screenshots: {
        enabled: true,
        on_failure: true,
        on_error: true,
        path: `${process.env.DRUPAL_NIGHTWATCH_OUTPUT}/screenshots`,
      },
      end_session_on_fail: false,
      skip_testcases_on_fail: false,
      enable_fail_fast: true,
    },
  },
};
