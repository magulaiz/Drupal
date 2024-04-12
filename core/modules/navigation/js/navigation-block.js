/**
 * @file
 * Navigation block behaviors.
 */

((Drupal, once, $) => {
  /**
   * Provide the summary info for the navigation block settings vertical tabs.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the navigation block settings summaries.
   */
  Drupal.behaviors.navigationBlockSettingsSummary = {
    attach() {
      /**
       * Sets the summary for all matched elements.
       *
       * @param {array} elements
       *   The DOM nodes to operate on.
       * @param {function} callback
       *   Either a function that will be called each time the summary is
       *   retrieved or a string (which is returned each time).
       *
       * @return {void}
       *
       * @fires event:summaryUpdated
       *
       * @listens event:formUpdated
       */
      function drupalSetSummary(elements, callback) {
        // To facilitate things, the callback should always be a function.
        // If it's not, we wrap it into an anonymous function which just
        // returns the value.
        if (typeof callback !== 'function') {
          const val = callback;
          callback = function summaryCallback() {
            return val;
          };
        }

        elements.forEach((element) => {
          // @todo jQuery is deprecated. Refactor jQuery out of this code when
          // Drupal core no longer depends on jQuery event triggers (these are
          // not able to be caught in native JavaScript).
          // @see https://www.drupal.org/project/drupal/issues/3052002
          $(element)
            .data('summaryCallback', callback.bind(element))
            // To prevent duplicate events, the handlers are first removed and
            // then (re-)added.
            .off('formUpdated.summary')
            .on('formUpdated.summary', () => {
              $(element).trigger('summaryUpdated');
            })
            // The actual summaryUpdated handler doesn't fire when the callback
            // is changed, so we have to do this manually.
            .trigger('summaryUpdated');
        });
      }

      /**
       * Create a summary for checkboxes in the provided context.
       *
       * @return {string}
       *   A string with the summary.
       */
      function checkboxesSummary() {
        const context = this.querySelectorAll ? this : document;
        const checkboxes = Array.from(
          context.querySelectorAll('input[type="checkbox"]:checked + label'),
        );
        const values = checkboxes ? checkboxes.map((i) => i.innerHTML) : [];
        if (!values.length) {
          values.push(Drupal.t('Not restricted'));
        }
        return values.join(', ');
      }

      drupalSetSummary(
        document.querySelectorAll(
          '[data-drupal-selector="edit-visibility-node-type"], [data-drupal-selector="edit-visibility-entity-bundlenode"], [data-drupal-selector="edit-visibility-language"], [data-drupal-selector="edit-visibility-user-role"], [data-drupal-selector="edit-visibility-response-status"]',
        ),
        checkboxesSummary,
      );

      drupalSetSummary(
        document.querySelectorAll(
          '[data-drupal-selector="edit-visibility-request-path"]',
        ),
        function requestCallback() {
          const context = this.querySelectorAll ? this : document;
          const pages = context.querySelectorAll(
            'textarea[name="visibility[request_path][pages]"]',
          );
          if (!pages.length || !pages[0].value) {
            return Drupal.t('Not restricted');
          }

          return Drupal.t('Restricted to certain pages');
        },
      );
    },
  };

  /**
   * Move a navigation block in the table between regions via select list.
   *
   * This behavior is dependent on the tableDrag behavior, since it uses the
   * objects initialized in that behavior to update the row.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the tableDrag behavior for navigation blocks in navigation
   *   block administration.
   */
  Drupal.behaviors.navigationBlockDrag = {
    attach(context) {
      // tableDrag is required, and we should be on the navigation block admin
      // page.
      if (
        typeof Drupal.tableDrag === 'undefined' ||
        typeof Drupal.tableDrag['navigation-blocks'] === 'undefined'
      ) {
        return;
      }

      /**
       * Function to check empty regions and toggle classes based on this.
       *
       * @param {Element} table
       *   The DOM object representing the table to inspect.
       * @param {Drupal.tableDrag.row} rowObject
       *   Drupal table drag row dropped.
       */
      function checkEmptyRegions(table, rowObject) {
        table
          .querySelectorAll('tr.region-message')
          .forEach(function each(item) {
            // If the dragged row is in this region, but above the message row,
            // swap it down one space.
            if (
              item.previousElementSibling === rowObject.element &&
              item.previousElementSibling.matches('tr')
            ) {
              // Prevent a recursion problem when using the keyboard to move rows
              // up.
              if (
                rowObject.method !== 'keyboard' ||
                rowObject.direction === 'down'
              ) {
                rowObject.swap('after', item);
              }
            }
            // item region has become empty.
            if (
              !item.nextElementSibling ||
              !item.nextElementSibling.matches('.draggable')
            ) {
              item.classList.remove('region-populated');
              item.classList.add('region-empty');
            }
            // item region has become populated.
            else if (item.matches('.region-empty')) {
              item.classList.remove('region-empty');
              item.classList.add('region-populated');
            }
          });
      }

      /**
       * Function to update the last placed row with the correct classes.
       *
       * @param {Element} table
       *   The DOM object representing the table to inspect.
       * @param {Drupal.tableDrag.row} rowObject
       *   Drupal table drag row dropped.
       */
      function updateLastPlaced(table, rowObject) {
        // Remove the color-success class from new navigation block if
        // applicable.
        table
          .querySelectorAll('.color-success')
          .forEach((item) => item.classList.remove('color-success'));
        if (!rowObject.element.matches('.drag-previous')) {
          table
            .querySelectorAll('.drag-previous')
            .forEach((item) => item.classList.remove('drag-previous'));
          rowObject.element.classList.add('drag-previous');
        }
      }

      /**
       * Update navigation block weights in the given region.
       *
       * @param {Element} table
       *   Table with draggable items.
       * @param {string} region
       *   Machine name of region containing navigation blocks to update.
       */
      function updateNavigationBlockWeights(table, region) {
        // Calculate minimum weight.
        let weight = -Math.round(
          table.querySelectorAll('.draggable').length / 2,
        );
        // Update the navigation block weights.
        table
          .querySelectorAll(`.region-${region}-message`)
          .forEach((messageRegion) => {
            if (messageRegion.classList.contains('region-title')) return;
            messageRegion
              .querySelectorAll('select.navigation-block-weight')
              // Increment the weight before assigning it to prevent using the
              // absolute minimum available weight. This way we always have an
              // unused upper and lower bound, which makes manually setting the
              // weights easier for users who prefer to do it that way.
              .forEach((select) => {
                weight = 1 + weight;
                select.value = weight;
              });
          });
      }

      const table = document.getElementById('navigation-blocks');
      // Get the navigation blocks tableDrag object.
      const tableDrag = Drupal.tableDrag['navigation-blocks'];
      // Add a handler for when a row is swapped, update empty regions.
      tableDrag.row.prototype.onSwap = Drupal.debounce(function swapRow() {
        checkEmptyRegions(table, this);
        updateLastPlaced(table, this);
      }, 200);

      // Add a handler so when a row is dropped, update fields dropped into
      // new regions.
      tableDrag.onDrop = function tableDrop() {
        // Use "region-message" row instead of "region" row because
        // "region-{region_name}-message" is less prone to regexp match errors.
        const regionRow = Array.from(
          document.querySelectorAll('tr.region-message'),
        ).filter((row) => {
          let passed = false;
          if (passed || row === this.rowObject.element) {
            passed = true;
            return;
          }
          return row;
        })[0];
        const regionName = regionRow.classList.value.replace(
          /([^ ]+[ ]+)*region-([^ ]+)-message([ ]+[^ ]+)*/,
          '$2',
        );
        // NOTE: If we ever return to multiple regions, we need to add back
        // handling for multiple regions. First to verify valid region names,
        // second to update the region value.
        updateNavigationBlockWeights(table, regionName);
      };

      // Add the behavior to each region select list.
      const navSelect = once(
        'navigation-block-region-select',
        'select.navigation-block-region-select',
        context,
      );
      if (navSelect.length) {
        navSelect.shift().addEventListener('change', function regionChange() {
          // Make our new row and select field.
          const row = this.closest('tr');
          // Find the correct region and insert the row as the last in the
          // region.
          // eslint-disable-next-line new-cap
          tableDrag.rowObject = new tableDrag.row(row[0]);
          const regionMessage = table.querySelector(
            `.region-${this.value}-message`,
          );
          const regionItems = Array.from(
            document.querySelectorAll('.region-message, .region-title'),
          ).filter((item) => {
            let passed = false;
            if (passed || item === regionMessage) {
              passed = true;
              return;
            }
            return item;
          });
          if (regionItems.length) {
            regionItems[regionItems.length - 1].after(row);
          }
          // We found that regionMessage is the last row.
          else {
            regionMessage.after(row);
          }
          updateNavigationBlockWeights(table, this.value);
          // Modify empty regions with added or removed fields.
          checkEmptyRegions(table, tableDrag.rowObject);
          // Update last placed navigation block indication.
          updateLastPlaced(table, tableDrag.rowObject);
          // Show unsaved changes warning.
          if (!tableDrag.changed) {
            const newElement = Drupal.theme('tableDragChangedWarning');
            newElement.classList.add('navigation-block--animated');
            newElement.classList.add('navigation-block--hide');
            tableDrag.table.before(newElement);
            newElement.classList.replace(
              'navigation-block--hide',
              'navigation-block--show',
            );
            tableDrag.changed = true;
          }
          // Remove focus from selectbox.
          this.dispatchEvent('blur');
        });
      }
    },
  };
})(Drupal, once, jQuery);
