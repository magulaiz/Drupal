// eslint-disable-next-line max-classes-per-file
((Drupal) => {
  Drupal.DrupalModel = class extends Backbone.Model {
    constructor() {
      super();
      this.modelId = (Math.random() + 1).toString(36).substring(7);
      console.log('i am the constructor in lil model with id.', this.modelId);
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

  Drupal.DrupalView = class extends Backbone.View {
    constructor(...args) {
      super();
      console.log('i am the constructor in lil view.');
      // if (typeof args[0] === 'object') {
      // }
    }

    addChangeListener(callback, modelProperty) {
      if (!modelProperty) {
        // listen for document `model-${this.model-modelId}-change` to respond with callback
        document.addEventListener(
          `model-${this.model.modelId}-change`,
          callback(),
        );
      } else {
        // listen for document `model-${this.model-modelId}-change-${modelProperty}` to respond with callback
        document.addEventListener(
          `model-${this.model.modelId}-change-${modelProperty}`,
          callback(),
        );
      }
    }
  };
})(Drupal);
