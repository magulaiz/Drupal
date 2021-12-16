// eslint-disable-next-line max-classes-per-file
((Drupal, Backbone, $) => {

  const deprecatedModelPrototypeProperties = [
    'on',
    'listenTo',
    'off',
    'stopListening',
    'once',
    'listenToOnce',
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
      this.values = {};
      this.changed = {};
      this._previousValues = {};

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
      return this.values[property];
    }

    // @todo documentation + consider adding deprecations to simplify this.
    set(key, val, options) {
      if (key == null) {
        return this;
      }

      // Handle both `"key", value` and `{key: value}` -style arguments.
      let attrs;
      if (typeof key === 'object') {
        attrs = key;
        options = val;
      } else {
        (attrs = {})[key] = val;
      }

      options || (options = {});

      // Run validation.
      if (!this._validate(attrs, options)) {
        return false;
      }

      // Extract attributes and options.
      const unset = options.unset;
      const silent = options.silent;
      const changes = [];
      const changing = this._changing;
      this._changing = true;

      if (!changing) {
        this._previousValues = Object.assign({}, this.values);
        this.changed = {};
      }

      const current = this.values;
      const changed = this.changed;
      const prev = this._previousValues;

      // For each `set` attribute, update or delete the current value.
      for (const attr in attrs) {
        val = attrs[attr];
        if (!_.isEqual(current[attr], val)) changes.push(attr);
        if (!_.isEqual(prev[attr], val)) {
          changed[attr] = val;
        } else {
          delete changed[attr];
        }
        unset ? delete current[attr] : current[attr] = val;
      }

      // Update the `id`.
      if (this.idAttribute in attrs) {
        this.id = this.get(this.idAttribute);
      }

      // Trigger all relevant attribute changes.
      if (!silent) {
        if (changes.length) this._pending = options;
        for (var i = 0; i < changes.length; i++) {
          this.triggerEvent(`model-${this.modelId}-change`);
          this.triggerEvent(`model-${this.modelId}-change-${changes[i]}`);
          super.trigger('change:' + changes[i], this, current[changes[i]], options);
        }
      }

      // You might be wondering why there's a `while` loop here. Changes can
      // be recursively nested within `"change"` events.
      if (changing) {
        return this;
      }
      if (!silent) {
        while (this._pending) {
          options = this._pending;
          this._pending = false;
          super.trigger('change', this, options);
        }
      }
      this._pending = false;
      this._changing = false;
      return this;
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
      return this._previousValues[property];
    }

    trigger(...args) {
      Drupal.deprecationError({
        message: `Drupal.DrupalModel.trigger is deprecated in drupal:9.4.0 and will be removed from drupal:10.0.0. Use Drupal.DrupalModel.triggerEvent instead.`,
      });
      return super.trigger.apply(this, args);
    }

    get attributes() {
      Drupal.deprecationError({
        message: 'Drupal.DrupalModel.attributes is deprecated in drupal:9.4.0 and will be removed from drupal:10.0.0. Use Drupal.DrupalModel.values instead.',
      });
      return this.values;
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
