// eslint-disable-next-line max-classes-per-file
((Drupal, Backbone, $) => {

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

  /**
   * Data object used for used for storing state of interactive UIs.
   *
   * This is a subset of Backbone Model.
   *
   * @type {{new(): Drupal.DrupalModel, modelId, allowSetChanged: boolean, changed: {}, previousItems: {}, prototype: DrupalModel}}
   *
   * @internal
   *   This class is provided for BC but it will likely be removed in the
   *   future.
   */
  Drupal.DrupalModel = class extends Backbone.Model {
    constructor() {
      super();
      this.modelId = (Math.random() + 1).toString(36).substring(7);
      this.allowSetChanged = true;
      this.changed = {};
      this.previousItems = {};

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
        this.allowSetChanged = false;
        this.changed = args[0];
        // Individually set each option specified in the object.
        Object.keys(args[0]).forEach((key) => {
          this.set(key, args[0][key]);
        });
        this.allowSetChanged = true;

        // If there is a second argument
      } else if (args[1]) {
        // eslint-disable-next-line prefer-destructuring
        const [property, value] = args;

        this.previousItems[property] = this[property];
        this[property] = value;

        if (this.allowSetChanged) {
          this.changed = {};
          this.changed[args[0]] = args[1];
        }
        this.triggerEvent(`model-${this.modelId}-change`);
        this.triggerEvent(`model-${this.modelId}-change-${args[0]}`);
      }
    }

    triggerEvent(type, cancelable = false) {
      // console.log('trigger event type', type);
      const event = new CustomEvent(type, {
        bubbles: true,
        cancelable,
      });

      return document.dispatchEvent(event);
    }

    previous(property) {
      return this.previousItems[property];
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

  /**
   * Data object for rendering interactive UIs.
   *
   * @type {{new(...[*]): Drupal.DrupalView, _$el: null, prototype: *, _removeElement, delegate, undelegateEvents, undelegate, _setAttributes}}
   *
   * @internal
   *   This class is provided for BC but it will likely be removed in the
   *   future.
   */
  Drupal.DrupalView = class extends Backbone.View {
    constructor(...args) {
      super();
      this._$el = null;
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

  // The following 5 overrides are needed to eliminate internal use of the
  // deprecated $el property.
  Drupal.DrupalView.prototype._removeElement = function() {
    $(this.el).remove();
  };
  Drupal.DrupalView.prototype.delegate = function(el) {
    $(this.el).on(eventName + '.delegateEvents' + this.cid, selector, listener);
    return this;
  };
  Drupal.DrupalView.prototype.undelegateEvents = function() {
    if (this.el) $(this.el).off('.delegateEvents' + this.cid);
    return this;
  };
  Drupal.DrupalView.prototype.undelegate = function(eventName, selector, listener) {
    $(this.el).off(eventName + '.delegateEvents' + this.cid, selector, listener);
    return this;
  };
  Drupal.DrupalView.prototype._setAttributes = function(attributes) {
    $(this.el).attr(attributes);
  }
})(Drupal, Backbone, jQuery);
