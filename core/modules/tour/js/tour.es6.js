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
    currentTour: [],
  };

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
      // this.model.set('tour', filteredTour);
    }
    console.log(`filtered tour: ${filteredTour.length}`);
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
        const shepherdTour = new Shepherd.Tour(settings.tourShepherdConfig);
        Drupal.tour.currentTour = settings._tour_internal;

        if (settings._tour_internal) {
          console.log('settings tour internal is true');
          _removeIrrelevantTourItems(settings._tour_internal);
          $(context).find('#toolbar-tab-tour').toggleClass('hidden', false);
          $(context)
            .find('#toolbar-tab-tour')[0]
            .addEventListener(
              'click',
              function () {
                shepherdTour.start();
              },
              false,
            );
          console.log(Drupal.tour.currentTour.length);
        }
        const tourItems = Drupal.tour.currentTour;

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
          shepherdTour.addStep(tourItemOptions);
        });

        shepherdTour.on('cancel', () => {
          console.log('tour is cancelled');
        });
        shepherdTour.on('complete', () => {
          console.log('tour is complete');
        });
      });
    },
  };

  // //TODO KEEP THIS BELOW
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
