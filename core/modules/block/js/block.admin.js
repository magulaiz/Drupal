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
      const $inputFilter = $(
        once(
          'block-filter-region-text',
          '[data-drupal-selector="edit-search-blocks"]',
        ),
      );
      const $table = $('#blocks');
      const $listItems = $table.find('tbody tr.draggable');

      if ($listItems.length === 0) {
        return;
      }

      const filterCallback = (e) => {
        const query = e.target.value.toLowerCase();
        // Clear the empty message when typing starts.
        $table.find('#block-filter-region-empty-message').remove();

        $listItems.each((index, tr) => {
          const $tr = $(tr);
          try {
            // Query the block label and region name.
            const textToBeQueried = `${tr.children[0].textContent} ${tr.children[1].textContent}`;
            $tr.toggle(textToBeQueried.toLowerCase().includes(query));
          } catch (error) {
            // If a problem occurs, default to showing the row.
            $tr.show();
          }
          const regionName = $tr.data('parentRegion');
          const regionElement = $(`tr[data-region="${regionName}"]`);
          const hasBlockVisible = $(
            `tr[data-parent-region="${regionName}"]:visible`,
          ).length;
          regionElement.toggle(hasBlockVisible > 0);
        });

        // Hidden regions that don't have any blocks displayed.
        $table.find('tr.region-message:visible').each((i, el) => {
          $(el).hide();
          const regionSelector = $(el).data('regionMessage');
          $(`[data-region="${regionSelector}"]`).hide();
        });
        // If there are no blocks, display empty message.
        if ($listItems.find(':visible').length === 0) {
          $table.append(Drupal.theme('blockFilterEmptyMessage'));
        }
      };

      function preventEnter(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          e.stopPropagation();
        }
      }

      $inputFilter.on('keyup', debounce(filterCallback, 200));
      $inputFilter.on('keydown', preventEnter);
    },
  };
})(jQuery, Drupal, Drupal.debounce, once);
