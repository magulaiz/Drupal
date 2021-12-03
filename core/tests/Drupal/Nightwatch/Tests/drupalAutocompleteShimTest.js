// cSpell:ignore bratbrass appleanteaterartifactampersandbrat
// Test the shimmed and deprecated Drupal.autocomplete.

module.exports = {
  '@tags': ['core'],
  before(browser) {
    browser.drupalInstall().drupalLoginAsAdmin(() => {
      browser
        .drupalRelativeURL('/admin/modules')
        .setValue('input[type="search"]', 'jQuery Simulate')
        .waitForElementVisible(
          'input[name="modules[jquery_simulate][enable]"]',
          1000,
        )
        .click('input[name="modules[jquery_simulate][enable]"]')
        .click('input[type="submit"]')
        .drupalRelativeURL('/admin/modules')
        .setValue('input[type="search"]', 'autocomplete Shim Test')
        .waitForElementVisible(
          'input[name="modules[autocomplete_shim_test][enable]"]',
          1000,
        )
        .click('input[name="modules[autocomplete_shim_test][enable]"]')
        .click('input[type="submit"]');
    });
  },
  beforeEach(browser) {
    browser
      // Go to page with an input that can be initialized after page load.
      // This makes it easier to test Drupal.autocomplete overrides.
      .drupalRelativeURL(
        '/autocomplete-shim-test',
      )
      .waitForElementPresent('#autocomplete-wrap1', 1000);
  },
  after(browser) {
    browser.drupalUninstall();
  },
  splitValues: (browser) => {
    browser.executeAsync(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function (done) {
        const toReturn = {};
        // Override splitValues to split with pipes instead of commas.
        // eslint-disable-next-line func-names
        Drupal.autocomplete.splitValues = function (value) {
          const result = [];
          let quote = false;
          let current = '';
          const valueLength = value.length;
          let character;

          for (let i = 0; i < valueLength; i++) {
            character = value.charAt(i);
            if (character === '"') {
              current += character;
              quote = !quote;
            } else if (character === '|' && !quote) {
              result.push(current.trim());
              current = '';
            } else {
              current += character;
            }
          }
          if (value.length > 0) {
            result.push(current.trim());
          }

          return result;
        };
        const items = [
          'apple',
          'anteater',
          'artifact',
          'ampersand',
          'boy',
          'bing',
          'brat',
          'city',
        ];
        const input = document.querySelector('#autocomplete');
        Drupal.Autocomplete.initialize(input);
        const instance = Drupal.Autocomplete.instances.autocomplete;
        instance._internal_object.options.source = (term, response) => {
          response(items.filter((item) => item.includes(term)));
        };
        instance._internal_object.options.searchDelay = 0;

        const event = new Event('input', {
          bubbles: true,
          cancelable: true,
        });

        input.value = 'a';
        input.dispatchEvent(event);
        setTimeout(() => {
          let searchResults = '';
          toReturn.numResultsSearchA =
            Array.from(input.parentNode.querySelectorAll('li')).filter(
              (item) => {
                if (!item.hidden) {
                  searchResults += item.innerText;
                  return true;
                }
                return false;
              },
            ).length === 5;
          toReturn.searchResultsA =
            searchResults === 'appleanteaterartifactampersandbrat';
          input.value = 'a,a';
          input.dispatchEvent(event);
          setTimeout(() => {
            toReturn.numResultsSearchComma =
              Array.from(input.parentNode.querySelectorAll('li')).filter(
                (item) => !item.hidden,
              ).length === 0;
            input.value = 'a|a';
            input.dispatchEvent(event);
            setTimeout(() => {
              let searchResultsPipe = '';
              toReturn.numResultsSearchPipe =
                Array.from(input.parentNode.querySelectorAll('li')).filter(
                  // eslint-disable-next-line max-nested-callbacks
                  (item) => {
                    if (!item.hidden) {
                      searchResultsPipe += item.innerText;
                      return true;
                    }
                    return false;
                  },
                ).length === 5;
              toReturn.searchResultsPipe =
                searchResultsPipe === 'appleanteaterartifactampersandbrat';
              done(toReturn);
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          numResultsSearchA: '5 results searching for "a"',
          searchResultsA: 'the expected results searching for "a"',
          numResultsSearchComma: '0 results searching for "a,a"',
          numResultsSearchPipe: '5 results searching for a|a',
          searchResultsPipe: 'the expected results searching for "a|a"',
        };
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
      },
    );
  },
  extractLastTerm: (browser) => {
    browser.executeAsync(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function (done) {
        const toReturn = {};
        // Override splitValues to split with pipes instead of commas.
        // eslint-disable-next-line func-names
        Drupal.autocomplete.extractLastTerm = function (term) {
          return `${this.splitValues(term).pop()}r`;
        };
        const items = [
          'apple',
          'anteater',
          'artifact',
          'ampersand',
          'boy',
          'bing',
          'brat',
          'brass',
          'city',
        ];
        const input = document.querySelector('#autocomplete');
        Drupal.Autocomplete.initialize(input);
        const instance = Drupal.Autocomplete.instances.autocomplete;
        instance._internal_object.options.source = (term, response) => {
          response(items.filter((item) => item.includes(term)));
        };
        instance._internal_object.options.searchDelay = 0;

        const event = new Event('input', {
          bubbles: true,
          cancelable: true,
        });

        input.value = 'a';
        input.dispatchEvent(event);
        setTimeout(() => {
          let searchResults = '';
          toReturn.numResultsSearchA =
            Array.from(input.parentNode.querySelectorAll('li')).filter(
              (item) => {
                if (!item.hidden) {
                  searchResults += item.innerText;
                  return true;
                }
                return false;
              },
            ).length === 1;
          toReturn.searchResultsA = searchResults === 'artifact';
          input.value = 'c';
          input.dispatchEvent(event);
          setTimeout(() => {
            toReturn.numResultsSearchC =
              Array.from(input.parentNode.querySelectorAll('li')).filter(
                (item) => !item.hidden,
              ).length === 0;
            toReturn.words = input.parentNode.querySelector('ul').innerText;
            input.value = 'b';
            input.dispatchEvent(event);
            setTimeout(() => {
              let searchResultsPipe = '';
              toReturn.numResultsSearchB =
                Array.from(input.parentNode.querySelectorAll('li')).filter(
                  // eslint-disable-next-line max-nested-callbacks
                  (item) => {
                    if (!item.hidden) {
                      searchResultsPipe += item.innerText;
                      return true;
                    }
                    return false;
                  },
                ).length === 2;
              toReturn.searchResultsB = searchResultsPipe === 'bratbrass';
              done(toReturn);
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          numResultsSearchA: '1 results searching for "a"',
          searchResultsA: 'the expected results searching for "a"',
          numResultsSearchC: '0 results searching for "c"',
          numResultsSearchB: '2 results searching for b',
          searchResultsB: 'the expected results searching for "b"',
        };
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
      },
    );
  },
  'unsupported use of custom widget': (browser) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $ = jQuery;
        $.widget('custom.categoryComplete', $.ui.autocomplete, {
          // eslint-disable-next-line object-shorthand
          _create() {
            // create is not supported, so this should trigger an error.
          },
        });
        return {};
      },
      [],
      (result) => {
        browser.assert.equal(
          result.status,
          -1,
          'Unsupported uses custom widget extending autocomplete throws error',
        );
        browser.assert.ok(
          result.value.message.includes(
            'Unsupported use of $.widget to extend autocomplete. The following constructor properties are not supported: _create',
          ),
          'custom widget error specifies unusable properties',
        );
      },
    );
  },
  'test deprecation': (browser) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $element = jQuery('#autocomplete').autocomplete('widget');
        return {};
      },
      [],
      (result) => {
        browser.assert.deprecationErrorExists(
          'The autocomplete() function is deprecated in drupal:9.4.0 and is removed from drupal:10.0.0. Use the API provided by core/a11y_autocomplete instead. See https://www.drupal.org/node/3083715',
        );
      },
    );
  },
  'blacklist deprecation': (browser) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $element = jQuery('#autocomplete');
        $element.attr('data-autocomplete-first-character-blacklist', '!');
        if ($element[0].hasAttribute('data-autocomplete-input')) {
          Drupal.Autocomplete.initialize($element[0]);
        } else {
          $element.autocomplete();
        }
      },
      [],
      () => {
        browser.assert.deprecationErrorExists(
          'The data-autocomplete-first-character-blacklist attribute is deprecated in drupal:9.4.0 and is removed from drupal:10.0.0. Use data-autocomplete-first-character-ignore-list instead See https://www.drupal.org/node/3250730',
        );
      },
    );
  },
};
