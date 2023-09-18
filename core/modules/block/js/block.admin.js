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
   * Theme function for the block filter empty message on the block layout page.
   */
  Drupal.theme.blockFilterEmptyMessage = function () {
    const message = Drupal.t(
      `There are no blocks matching the filter conditions.`,
    );
    return `
      <tr id="block-filter-region-empty-message">
        <td colspan='5'>
          <div class="text-align-center">
            <strong>${message}</strong>
          </div>
        </td>
      </tr>
    `;
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
    attach() {
      const hasBlockVisibleOnRegion = (regionName) => {
        const blocks = document.querySelectorAll(
          `tr[data-parent-region="${regionName}"]`,
        );
        return (
          Array.from(blocks).filter((tr) => tr.style.display !== 'none')
            .length > 0
        );
      };

      const filterCallback = (e) => {
        const table = document.getElementById('blocks');
        const listItems = table.querySelectorAll('tbody tr.draggable');

        if (listItems.length === 0) {
          return;
        }

        // Clear the empty message when typing starts.
        const emptyMessage = table.querySelector(
          '#block-filter-region-empty-message',
        );
        if (emptyMessage) {
          emptyMessage.remove();
        }

        const query = e.target.value.toLowerCase();

        listItems.forEach((tr) => {
          try {
            // Query the block label and region name.
            const textToBeQueried = `${tr.children[0].textContent} ${tr.children[1].textContent}`;
            tr.style.display = textToBeQueried.toLowerCase().includes(query)
              ? ''
              : 'none';
          } catch (error) {
            // If a problem occurs, default to showing the row.
            tr.style.display = '';
          }
        });

        const regionHeaders = table.querySelectorAll('.region-title');
        regionHeaders.forEach((el) => {
          const currentRegionName = el.dataset.region;
          const hasBlockVisible = hasBlockVisibleOnRegion(currentRegionName);
          const showRegionHeader = query === '' || hasBlockVisible;

          el.style.display = showRegionHeader ? '' : 'none';

          const regionEmptyMessage = el.nextElementSibling;
          let showEmptyRegion = false;
          if (
            query === '' &&
            table.querySelectorAll(
              `tr[data-parent-region="${currentRegionName}"]`,
            ).length === 0
          ) {
            showEmptyRegion = true;
          }

          if (showEmptyRegion) {
            regionEmptyMessage.classList.remove('region-populated');
            regionEmptyMessage.classList.add('region-empty');
          } else {
            regionEmptyMessage.classList.add('region-populated');
            regionEmptyMessage.classList.remove('region-empty');
          }
        });

        const visibleItems = Array.from(listItems).filter(
          (tr) => tr.style.display !== 'none',
        );
        if (visibleItems.length === 0) {
          table.insertAdjacentHTML(
            'beforeend',
            Drupal.theme('blockFilterEmptyMessage'),
          );
        }
      };

      function preventEnter(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          e.stopPropagation();
        }
      }

      const $inputFilter = once(
        'block-filter-region-text',
        '[data-drupal-selector="edit-search-blocks"]',
      );
      if ($inputFilter) {
        const inputFilterElement = $inputFilter[0];
        if (inputFilterElement) {
          inputFilterElement.addEventListener(
            'keyup',
            debounce(filterCallback, 200),
          );
          inputFilterElement.addEventListener('keydown', preventEnter);
        }
      }
    },
  };
})(jQuery, Drupal, Drupal.debounce, once);
