/**
 * @file
 * Block admin behaviors.
 */

(function ($, Drupal, debounce, once) {
  /**
   * Filters the block list by a text input search string.
   *
   * The text input will have the selector `input.block-filter-text`.
   *
   * The target element to do searching in will be in the selector
   * `input.block-filter-text[data-element]`
   *
   * The text source where the text should be found will have the selector
   * `.block-filter-text-source`
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the block filtering.
   */
  Drupal.behaviors.blockFilterByText = {
    attach(context, settings) {
      const $input = $(once('block-filter-text', 'input.block-filter-text'));
      const $table = $($input.attr('data-element'));
      let $filterRows;

      /**
       * Filters the block list.
       *
       * @param {jQuery.Event} e
       *   The jQuery event for the keyup event that triggered the filter.
       */
      function filterBlockList(e) {
        const query = e.target.value.toLowerCase();

        /**
         * Shows or hides the block entry based on the query.
         *
         * @param {number} index
         *   The index in the loop, as provided by `jQuery.each`
         * @param {HTMLElement} label
         *   The label of the block.
         */
        function toggleBlockEntry(index, label) {
          const $row = $(label).parent().parent();
          const textMatch = label.textContent.toLowerCase().includes(query);
          $row.toggle(textMatch);
        }

        // Filter if the length of the query is at least 2 characters.
        if (query.length >= 2) {
          $filterRows.each(toggleBlockEntry);
          Drupal.announce(
            Drupal.formatPlural(
              $table.find('tr:visible').length - 1,
              '1 block is available in the modified list.',
              '@count blocks are available in the modified list.',
            ),
          );
        } else {
          $filterRows.each(function (index) {
            $(this).parent().parent().show();
          });
        }
      }

      if ($table.length) {
        $filterRows = $table.find('div.block-filter-text-source');
        $input.on('keyup', debounce(filterBlockList, 200));
      }
    },
  };

  /**
   * Highlights the block that was just placed into the block listing.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the block placement highlighting.
   */
  Drupal.behaviors.blockHighlightPlacement = {
    attach(context, settings) {
      // Ensure that the block we are attempting to scroll to actually exists.
      if (settings.blockPlacement && $('.js-block-placed').length) {
        once(
          'block-highlight',
          '[data-drupal-selector="edit-blocks"]',
          context,
        ).forEach((container) => {
          const $container = $(container);
          // Just scrolling the document.body will not work in Firefox. The html
          // element is needed as well.
          $('html, body').animate(
            {
              scrollTop:
                $('.js-block-placed').offset().top -
                $container.offset().top +
                $container.scrollTop(),
            },
            500,
          );
        });
      }
    },
  };

  /**
   * Filter the block list on block layout page by a text input search string.
   *
   * The target elements to search are block label and block region name.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the block filtering on block layout page.
   */
  Drupal.behaviors.blockFilterRegionText = {
    attach(context) {
      let firstVisibleRegion = null;
      let isFocusingFilterElement = false;
      const gotoFiltered = document.querySelector('#goto-filtered');
      const $inputFilter = once(
        'block-filter-region-text',
        '[data-drupal-selector="edit-search-blocks"]',
      );

      let inputFilterElement;
      if (
        $inputFilter.length === 0 ||
        !(inputFilterElement = $inputFilter[0])
      ) {
        return;
      }

      /**
       * Users can filter blocks and regions and goes to bottom of page,
       * to make easier change the filter sticky the filter input,
       * below the Drupal menu.
       */
      const moveInputFilterWhenScrolling = () => {
        // Sticky input filter when scroll down.
        document.addEventListener('DOMContentLoaded', function () {
          const toolbarHeight =
            document.getElementById('toolbar-bar').clientHeight;
          const submenu = document.getElementById(
            'toolbar-item-administration-tray',
          ).clientHeight;
          const paddingTop = toolbarHeight + submenu;
          const inputFilterForm = document.querySelector(
            'fieldset[data-drupal-selector="edit-filters"]',
          );
          const topFilter = paddingTop;
          const sticky = inputFilterForm.offsetTop;
          let lastVisiblePosition;

          function calcInputFilterPositionOnScroll() {
            const scrollPosition = window.scrollY + paddingTop;
            if (scrollPosition >= sticky) {
              inputFilterForm.style.top = `${topFilter}px`;
              inputFilterForm.classList.add('filter-block');
              document.getElementById(
                'blocks',
              ).style.marginTop = `${inputFilterForm.offsetHeight}px`;
            } else {
              document.getElementById('blocks').style.marginTop = 0;
              lastVisiblePosition = window.scrollY;
              inputFilterForm.classList.remove('filter-block');
            }
          }
          window.onscroll = calcInputFilterPositionOnScroll;
        });
      };

      /**
       * Function to count the blocks and their status by region.
       *
       * @param {string} regionName
       *   The name of the region to get the status.
       * @return {{total: number, visible: number, invisible: number}}
       *   Return an object with regions blocks status.
       */
      const getRegionBlocksStatus = (regionName) => {
        const blocks = document.querySelectorAll(
          `tr[data-parent-region="${regionName}"]`,
        );
        const visible = Array.from(blocks).filter(
          (tr) => tr.style.display !== 'none',
        ).length;
        return {
          total: blocks.length,
          visible,
          invisible: blocks.length - visible,
        };
      };

      const preventEnter = (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          e.stopPropagation();
        }
      };

      /**
       * Function to reset the state of blocks and filter control.
       *
       * Reset all button elements to closed eyes icon and make any row,
       * be hidden by the filter.
       */
      const resetBlockRegionState = () => {
        document
          .querySelectorAll('.js-filter-result-toggle')
          .forEach((el) => el.classList.remove(['js-filter-result-toggle']));
        document
          .querySelectorAll('.region-filter-control')
          .forEach((el) =>
            el.classList.remove(['region-filter-control-opened']),
          );
      };

      /**
       * Update regions and their blocks states.
       *
       * Each region updating the filtered blocks message status.
       * Also, update the region empty message status.
       *
       * @param {string} query
       *   The value of the filter typed by the user.
       * @param {Node} table
       *   The updated dom element block with all regions.
       */
      const updateRegions = (query, table) => {
        const regionHeaders = table.querySelectorAll('.region-title');
        regionHeaders.forEach((el) => {
          const currentRegionName = el.dataset.region;
          const { total, visible, invisible } =
            getRegionBlocksStatus(currentRegionName);
          // Set first visible region to be used on the help go to element link.
          if (
            total > 0 &&
            visible > 0 &&
            firstVisibleRegion === null &&
            query !== ''
          ) {
            firstVisibleRegion = el;
          }

          // Update the region filter status.
          const filteredRegionStatus = document.querySelector(
            `[data-drupal-selector="region-filtered-quantity-${currentRegionName}"]`,
          );
          if (total > 0) {
            const toggleAction =
              query !== '' && total !== visible ? 'add' : 'remove';
            filteredRegionStatus.parentElement.parentElement.classList[
              toggleAction
            ]('filtered');
            filteredRegionStatus.textContent = Drupal.t(
              '@invisible filtered this region',
              { '@invisible': invisible },
            );
          }

          const regionEmptyMessage = el.nextElementSibling;
          let showEmptyRegion = false;
          if (
            (query === '' &&
              table.querySelectorAll(
                `tr[data-parent-region="${currentRegionName}"]`,
              ).length === 0) ||
            total === 0
          ) {
            showEmptyRegion = true;
          }
          regionEmptyMessage.style.display = showEmptyRegion ? '' : 'none';
        });
      };

      /**
       * Filter all draggable rows on the table.
       *
       * When filtering the row will be keep as visible if,
       * The name of the block/region matches with text typed.
       * Or, if the filtered control foe the regions is turned on,
       * no matter if block name and region match with typed text,
       * all the blocks of that region will be displayed.
       *
       * @param {string} query
       *   The text type.
       */
      const filterCallback = (query = null) => {
        if (query === null) {
          query = document
            .querySelector('[data-drupal-selector="edit-search-blocks"]')
            .value.toLowerCase();
        }

        const table = document.getElementById('blocks');
        const listItems = table.querySelectorAll('tbody tr.draggable');

        if (listItems.length === 0) {
          return;
        }

        listItems.forEach((tr) => {
          try {
            // Query the block label and region name.
            const textToBeQueried = `${tr.children[0].textContent} ${tr.children[1].textContent}`;
            // If the user clicked to display all blocks of the regions,
            // keep this blocks visible no matter if the query filter matched.
            const isVisibleByFilter = tr.classList.contains(
              'js-filter-result-toggle',
            );
            tr.style.display =
              textToBeQueried.toLowerCase().includes(query) || isVisibleByFilter
                ? ''
                : 'none';
          } catch (error) {
            // If a problem occurs, default to showing the row.
            tr.style.display = '';
          }
        });

        updateRegions(query, table);

        const visibleItems = Array.from(listItems).filter(
          (tr) => tr.style.display !== 'none',
        );

        if (visibleItems.length === 0) {
          gotoFiltered.classList.remove('link-to-element');
          gotoFiltered.textContent = Drupal.t(
            'There are no blocks matching the filter conditions.',
          );
          document.querySelector('#goto-filtered').style.display = 'block';
        }

        if (firstVisibleRegion !== null) {
          gotoFiltered.classList.add('link-to-element');
          gotoFiltered.textContent = Drupal.t('Go to items found.');
          document.querySelector('#goto-filtered').style.display = 'block';
        }
      };

      // In some cases the blocks searched are on the bottom of page,
      // to help users get there faster they can click on the link
      // below the input filter.
      gotoFiltered.addEventListener('click', (e) => {
        if (firstVisibleRegion === null) {
          return;
        }
        e.preventDefault();
        let offset = 0;
        const regionHeight = firstVisibleRegion.offsetHeight;
        let region = firstVisibleRegion;
        while (region) {
          offset += region.offsetTop;
          region = region.offsetParent;
        }
        window.scrollTo({
          top: offset - regionHeight,
        });
      });

      const toggleBlocksByRegion = (region, action = 'toggle') => {
        document
          .querySelector(`a[data-toggle-region="${region}"]`)
          .classList[action]('region-filter-control-opened');
        document
          .querySelectorAll(`tr[data-parent-region="${region}"]`)
          .forEach((tr) => {
            tr.classList[action]('js-filter-result-toggle');
          });
      };

      // Users can override the filter clicking on the eye action.
      // Toggling this element will display all blocks on the region,
      // no matters if filter applied.
      // When the filter is cleaned this button back to hidden states.
      document
        .querySelectorAll('.region-filter-control:not(.built)')
        .forEach((element) => {
          element.classList.add('built');
          element.addEventListener('click', () => {
            toggleBlocksByRegion(element.dataset.toggleRegion);
            filterCallback();
          });
        });

      inputFilterElement.addEventListener(
        'keyup',
        debounce((e) => filterCallback(e.target.value.toLowerCase()), 200),
      );

      // Add event to clear HTML5 x button.
      // But do the filter only on the click button because we already,
      // searching on keyup event with debounce.
      inputFilterElement.addEventListener('search', (e) => {
        if (e.target.value === '') {
          firstVisibleRegion = null;
          document.querySelector('#goto-filtered').style.display = 'none';
          filterCallback('');
          resetBlockRegionState();
        }
      });
      inputFilterElement.addEventListener('keydown', preventEnter);
      inputFilterElement.addEventListener('keyup', (e) => {
        if (e.target.value === '') {
          firstVisibleRegion = null;
          document.querySelector('#goto-filtered').style.display = 'none';
          resetBlockRegionState();
        }
      });

      // Focus the input filter only when scroll to the top finished.
      const checkScroll = function () {
        // Only focus the input if the user clicked to.
        if (window.scrollY === 0 && isFocusingFilterElement) {
          window.removeEventListener('scroll', checkScroll);
          inputFilterElement.focus();
          isFocusingFilterElement = false;
        }
      };
      window.addEventListener('scroll', checkScroll);

      // Do the filter after region changed by select field.
      const $selectRegionChange = once(
        'block-region-select-filter',
        'select.block-region-select',
      );
      if ($selectRegionChange.length > 0) {
        $selectRegionChange.forEach((selectElement) => {
          selectElement.addEventListener('change', (e) => {
            toggleBlocksByRegion(e.target.value, 'add');
            filterCallback($inputFilter[0].value);
          });
        });
      }

      // Extend block table drag event adding the filter callback.
      if (
        typeof Drupal.tableDrag !== 'undefined' &&
        typeof Drupal.tableDrag.blocks !== 'undefined'
      ) {
        const tableDrag = { ...Drupal.tableDrag.blocks };
        Drupal.tableDrag.blocks.onDrop = function () {
          if (
            tableDrag.rowObject == null ||
            tableDrag.rowObject.element === null
          ) {
            return;
          }
          const rowDropped = tableDrag.rowObject.element;
          const rowDroppedRegion = rowDropped.dataset.parentRegion;
          let prevRow = rowDropped.previousElementSibling;
          let newRow = null;
          const possibleRegionValues = [
            prevRow.dataset.parentRegion,
            prevRow.dataset.regionMessage,
            prevRow.dataset.region,
          ];
          if (possibleRegionValues.indexOf(rowDroppedRegion) === -1) {
            while (prevRow && prevRow.nodeName === 'TR') {
              if (
                prevRow.classList.contains('draggable') &&
                rowDroppedRegion === prevRow.dataset.parentRegion
              ) {
                newRow = prevRow;
                break;
              }
              prevRow = prevRow.previousElementSibling;
            }
            if (newRow) {
              tableDrag.rowObject.swap('after', newRow);
            }
          }
          toggleBlocksByRegion(rowDroppedRegion, 'add');
          filterCallback();
          tableDrag.onDrop();
        };
      }

      moveInputFilterWhenScrolling();
    },
  };
})(jQuery, Drupal, Drupal.debounce, once);
