// eslint-disable-next-line max-classes-per-file
((Drupal) => {
  Drupal.DrupalModel = class extends Backbone.Model {
    constructor() {
      console.log('i am the constructor in lil model.');
    }

    get(property) {
      return this.get(property);
    }

    set(...args) {
      if (typeof args[0] === 'object') {
        // Individually set each option specified in the object.
        Object.keys(args[0]).forEach((key) => {
          this[key] = args[0][key];
        });
        // If there is a second argument
      } else if (args[1].length) {
        // eslint-disable-next-line prefer-destructuring
        this[args[0]] = args[1];
      }
    }
  };
  Drupal.DrupalView = class extends Backbone.View {
    constructor() {
      console.log('i am the constructor in lil view.');
    }
  };
})(Drupal);
