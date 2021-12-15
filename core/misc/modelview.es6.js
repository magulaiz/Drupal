// eslint-disable-next-line max-classes-per-file
((Drupal, Backbone) => {

  const deprecatedModelPrototypeProperties = [
    'on',
    'listenTo',
    'off',
    'stopListening',
    'once',
    'listenToOnce',
    'trigger',
    'bind',
    'unbind',
    'changed',
    'validationError',
    'idAttribute',
    'cidPrefix',
    'toJSON',
    'sync',
    'escape',
    'has',
    'matches',
    'unset',
    'clear',
    'hasChanged',
    'changedAttributes',
    'previous',
    'previousAttributes',
    'fetch',
    'save',
    'destroy',
    'url',
    'parse',
    'clone',
    'isNew',
    'isValid',
    '_validate',
    'keys',
    'values',
    'pairs',
    'invert',
    'pick',
    'omit',
    'chain',
    'isEmpty',
  ];

  Drupal.DrupalModel = class extends Backbone.Model {
    constructor() {
      super();
      this.modelId = (Math.random() + 1).toString(36).substring(7);

      if (this.preinitialize !== Backbone.Model.prototype.preinitialize) {
        Drupal.deprecationError({
          message: `Drupal.DrupalModel.preinitialize is deprecated in drupal:9.4.0 and will be removed from drupal:10.0.0. Use constructor() instead.`,
        });
      }
      if (this.initialize !== Backbone.Model.prototype.initialize) {
        Drupal.deprecationError({
          message: `Drupal.DrupalModel.initialize is deprecated in drupal:9.4.0 and will be removed from drupal:10.0.0. Use constructor() instead.`,
        });
      }
    }

    get(property) {
      return this[property];
    }

    set(...args) {
      if (typeof args[0] === 'object') {
        // Individually set each option specified in the object.
        Object.keys(args[0]).forEach((key) => {
          this.set(key, args[0][key]);
        });
        // If there is a second argument
      } else if (args[1]) {
        // eslint-disable-next-line prefer-destructuring
        this[args[0]] = args[1];
        this.triggerEvent(`model-${this.modelId}-change`);
        this.triggerEvent(`model-${this.modelId}-change-${args[0]}`);
      }
    }

    triggerEvent(type, cancelable = false) {
      console.log('trigger event type', type);
      const event = new CustomEvent(type, {
        bubbles: true,
        cancelable,
      });

      return document.dispatchEvent(event);
    }
  };
  deprecatedModelPrototypeProperties.forEach((property) => {
    if (typeof Drupal.DrupalModel.prototype[property] === 'function') {
      // Add deprecation messages to backbone functions.
      const originalFunction = Drupal.DrupalModel.prototype[property];
      Drupal.DrupalModel.prototype[property] = function (...args) {
        Drupal.deprecationError({
          message: `Drupal.DrupalModel.${property} is deprecated in drupal:9.4.0 and will be removed from drupal:10.0.0.`,
        });
        return originalFunction.apply(this, args);
      };
    }
  });

  Drupal.DrupalView = class extends Backbone.View {
    constructor(...args) {
      super();
      console.log('i am the constructor in lil view.');
      // if (typeof args[0] === 'object') {
      // }
    }

    /**
     * Set the element and re-delegate the view's events on the new element.
     *
     * @param {Element|jQuery} element
     *   The new element for the view.
     *
     * @todo some logic from Backbone should be moved here.
     */
    setElement(element) {
      return super.setElement(element);
    }

    addChangeListener(callback, modelProperty) {
      if (!modelProperty) {
        // listen for document `model-${this.model-modelId}-change` to respond with callback
        document.addEventListener(
          `model-${this.model.modelId}-change`,
          callback(),
        );
      } else {
        if (!callback) {
          debugger;
        }
        // listen for document `model-${this.model-modelId}-change-${modelProperty}` to respond with callback
        document.addEventListener(
          `model-${this.model.modelId}-change-${modelProperty}`,
          callback.bind(this),
        );
      }
    }
  };
  Drupal.DrupalView.prototype = Drupal.deprecatedProperty({
    target: Drupal.DrupalView.prototype,
    deprecatedProperty: '$el',
    message: 'Drupal.DrupalView.$el is deprecated in drupal:9.4.0 and will be removed from drupal:10.0.0. Use Drupal.DrupalView.el instead.',
  });
})(Drupal, Backbone);
