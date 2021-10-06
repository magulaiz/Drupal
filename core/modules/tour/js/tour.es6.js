/**
 * @file
 * Attaches behaviors for the Tour module's toolbar tab.
 */

(($, Drupal, settings, document, Shepherd) => {
  const queryString = decodeURI(window.location.search);

  /**
   * @namespace
   */
  Drupal.tour = Drupal.tour || {
    /**
     * The current tour for the page provided by settings.tour_internal.
     *
     * @type {Array}
     */
    currentTour: [],
    /**
     * Indicates whether the tour is currently running.
     *
     * @type {boolean}
     */
    isActive: false,
    /**
     * Indicates which tour is the active one (necessary to cleanly stop).
     *
     * @type {Array}
     */
    activeTour: [],
  };

  /**
   * Removes tour items for elements that don't have matching page elements.
   *
   * Or that are explicitly filtered out via the 'tips' query string.
   *
   * @example
   * <caption>This will filter out tips that do not have a matching
   * page element or don't have the "bar" class.</caption>
   * http://example.com/foo?tips=bar
   *
   * @param {Object[]} tourItems
   *   An array containing tour Step config objects.
   *   The object properties relevant to this function:
   *   - classes {string}: A string of classes to be added to the tour step
   *     when rendered.
   *   - selector {string}: The selector a tour step is associated with.
   */
  function _removeIrrelevantTourItems(tourItems) {
    const tips = /tips=([^&]+)/.exec(queryString);
    const filteredTour = tourItems.filter((tourItem) => {
      // If the query parameter 'tips' is set, remove all tips that don't
      // have the matching class. The `tourItem` variable is a step config
      // object, and the 'classes' property is a ShepherdJS Step() config
      // option that provides a string.
      if (
        tips &&
        tourItem.hasOwnProperty('classes') &&
        tourItem.classes.indexOf(tips[1]) === -1
      ) {
        return false;
      }

      // If a selector is configured but there isn't a matching element,
      // return false.
      return !(tourItem.selector && !document.querySelector(tourItem.selector));
    });

    // If there are tours filtered, we'll have to update model.
    if (tourItems.length !== filteredTour.length) {
      filteredTour.forEach((filteredTourItem, filteredTourItemId) => {
        filteredTour[filteredTourItemId].counter = Drupal.t(
          '!tour_item of !total',
          {
            '!tour_item': filteredTourItemId + 1,
            '!total': filteredTour.length,
          },
        );

        if (filteredTourItemId === filteredTour.length - 1) {
          filteredTour[filteredTourItemId].cancelText = Drupal.t('End tour');
        }
      });
      Drupal.tour.currentTour = filteredTour;
    }
  }

  /**
   * Responsible for starting and stopping the tour.
   */
  function toggleTour() {
    if (Drupal.tour.isActive === false) {
      _removeIrrelevantTourItems(Drupal.tour.currentTour);
      const shepherdTour = new Shepherd.Tour(settings.tourShepherdConfig);
      shepherdTour.on('cancel', () => {
        Drupal.tour.isActive = false;
      });
      shepherdTour.on('complete', () => {
        Drupal.tour.isActive = false;
      });
      const tourItems = Drupal.tour.currentTour;

      if (tourItems.length) {
        // If Joyride is positioned relative to the top or bottom of an
        // element, and its secondary position is right or left, then the
        // arrow is also positioned right or left. Shepherd defaults to
        // center positioning the arrow.
        //
        // In most cases, this arrow positioning difference has
        // little impact. However, tours built with Joyride may have tips
        // using a higher level selector than the element the tip is
        // expected to point to, and relied on Joyride's arrow positioning
        // to align the arrow with the expected reference element. Joyride's
        // arrow positioning behavior is replicated here to prevent those
        // use cases from causing UI regressions.
        //
        // This modifier is provided here instead of TourViewBuilder (where
        // most position modifications are) because it includes adding a
        // JavaScript callback function.
        settings.tourShepherdConfig.defaultStepOptions.popperOptions.modifiers.push(
          {
            name: 'moveArrowJoyridePosition',
            enabled: true,
            phase: 'write',
            fn({ state }) {
              const { arrow } = state.elements;
              const { placement } = state;
              if (
                arrow &&
                /^top|bottom/.test(placement) &&
                /-start|-end$/.test(placement)
              ) {
                const horizontalPosition = placement.split('-')[1];
                const offset =
                  horizontalPosition === 'start'
                    ? 28
                    : state.elements.popper.clientWidth - 56;
                arrow.style.transform = `translate3d(${offset}px, 0px, 0px)`;
              }
            },
          },
        );
        tourItems.forEach((tourStepConfig, index) => {
          // Create the configuration for a given tour step by using values
          // defined in TourViewBuilder.
          // @see \Drupal\tour\TourViewBuilder::viewMultiple()
          const tourItemOptions = {
            title: tourStepConfig.title
              ? Drupal.checkPlain(tourStepConfig.title)
              : null,
            text: () => Drupal.theme('tourItemContent', tourStepConfig),
            attachTo: tourStepConfig.attachTo,
            buttons: [Drupal.tour.nextButton(shepherdTour, tourStepConfig)],
            classes: tourStepConfig.classes,
            index,
          };

          tourItemOptions.when = {
            show() {
              const nextButton =
                shepherdTour.currentStep.el.querySelector('footer button');

              // Drupal disables Shepherd's built in focus after item
              // creation functionality due to focus being set on the tour
              // item container after every scroll and resize event. In its
              // place, the 'next' button is focused here.
              nextButton.focus();

              // When Stable or Stable 9 are part of the active theme, the
              // Drupal.tour.convertToJoyrideMarkup() function is available.
              // This function converts Shepherd markup to Joyride markup,
              // facilitating the use of the Shepherd library that is
              // backwards compatible with customizations intended for
              // Joyride.
              // The Drupal.tour.convertToJoyrideMarkup() function is
              // internal, and will eventually be removed from Drupal core.
              if (Drupal.tour.hasOwnProperty('convertToJoyrideMarkup')) {
                Drupal.tour.convertToJoyrideMarkup(shepherdTour);
              }
            },
          };
          shepherdTour.addStep(tourItemOptions);
        });
        shepherdTour.start();
        Drupal.tour.isActive = true;
        Drupal.tour.activeTour = shepherdTour;
      }
    } else {
      Drupal.tour.activeTour.cancel();
    }
  }

  /**
   * Attaches the tour's toolbar tab behavior.
   *
   * It uses the query string for:
   * - tour: When ?tour=1 is present, the tour will start automatically after
   *   the page has loaded.
   * - tips: Pass ?tips=class in the url to filter the available tips to the
   *   subset which match the given class.
   *
   * @example
   * http://example.com/foo?tour=1&tips=bar
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attach tour functionality on `tour` events.
   */
  Drupal.behaviors.tour = {
    attach(context) {
      once('tour', 'body').forEach(() => {
        Drupal.tour.currentTour = settings._tour_internal;

        if (settings._tour_internal) {
          $(context).find('#toolbar-tab-tour').toggleClass('hidden', false);
          context
            .querySelector('#toolbar-tab-tour > button')
            // .find('#toolbar-tab-tour')
            .addEventListener(
              'click',
              function () {
                toggleTour();
              },
              false,
            );
        }
        // Start the tour immediately if toggled via query string.
        if (/tour=?/i.test(queryString)) {
          toggleTour();
        }
      });
    },
  };

  /**
   * Provides an object that will become the tour item's 'next' button.
   *
   * Similar to a theme function, themes can override this function to customize
   * the resulting button. Unlike a theme function, it returns an object instead
   * of a string, which is why it is not part of Drupal.theme.
   *
   * @param {Tour} shepherdTour
   *  A class representing a Shepherd site tour.
   * @param {Object} tourStepConfig
   *   An object generated in TourViewBuilder used for creating the options
   *   passed to `Tour.addStep(options)`.
   *   Contains the following properties:
   *   - id {string}: The tour.tip ID specified by its config
   *   - selector {string|null}: The selector of the element the tour step is
   *     attaching to.
   *   - module {string}: The module providing the tip plugin used by this step.
   *   - counter {string}: A string indicating which tour step this is out of
   *     how many total steps.
   *   - attachTo {Object} This is directly mapped to the `attachTo` Step()
   *     option. It has two properties:
   *     - element {string}: The selector of the element the step attaches to.
   *     - on {string}: a PopperJS compatible string to specify step position.
   *   - classes {string}: Will be added to the class attribute of the step.
   *   - body {string}: Markup that is mapped to the `text` Step() option. Will
   *     become the step content.
   *   - title {string}: is mapped to the `title` Step() option.
   *
   * @return {{classes: string, action: string, text: string}}
   *    An object structured in the manner Shepherd requires to create the
   *    'next' button.
   *
   * @see https://shepherdjs.dev/docs/Tour.html
   * @see \Drupal\tour\TourViewBuilder::viewMultiple()
   * @see https://shepherdjs.dev/docs/Step.html
   */
  Drupal.tour.nextButton = (shepherdTour, tourStepConfig) => {
    return {
      classes: 'button button--primary',
      text: tourStepConfig.cancelText
        ? tourStepConfig.cancelText
        : Drupal.t('Next'),
      action: tourStepConfig.cancelText
        ? shepherdTour.cancel
        : shepherdTour.next,
    };
  };

  /**
   * Theme function for tour item content.
   *
   * @param {Object} tourStepConfig
   *   An object generated in TourViewBuilder used for creating the options
   *   passed to `Tour.addStep(options)`.
   *   Contains the following properties:
   *   - id {string}: The tour.tip ID specified by its config
   *   - selector {string|null}: The selector of the element the tour step is
   *     attaching to.
   *   - module {string}: The module providing the tip plugin used by this step.
   *   - counter {string}: A string indicating which tour step this is out of
   *     how many total steps.
   *   - attachTo {Object} This is directly mapped to the `attachTo` Step()
   *     option. It has two properties:
   *     - element {string}: The selector of the element the step attaches to.
   *     - on {string}: a PopperJS compatible string to specify step position.
   *   - classes {string}: Will be added to the class attribute of the step.
   *   - body {string}: Markup that is mapped to the `text` Step() option. Will
   *     become the step content.
   *   - title {string}: is mapped to the `title` Step() option.
   *
   * @return {string}
   *   The tour item content markup.
   *
   * @see \Drupal\tour\TourViewBuilder::viewMultiple()
   * @see https://shepherdjs.dev/docs/Step.html
   */
  Drupal.theme.tourItemContent = (tourStepConfig) =>
    `${tourStepConfig.body}<div class="tour-progress">${tourStepConfig.counter}</div>`;
})(jQuery, Drupal, drupalSettings, document, window.Shepherd);
