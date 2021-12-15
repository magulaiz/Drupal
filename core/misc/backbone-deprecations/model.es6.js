/**
 * @file
 * Backbone deprecations.
 */

((Backbone, Drupal) => {
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
    'preinitialize',
    'initialize',
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
  deprecatedModelPrototypeProperties.forEach((property) => {
    const overrides = {};

    if (typeof Backbone.Model.prototype[property] === 'function') {
      // Add deprecation messages to backbone functions.
      const originalFunction = Backbone.Model.prototype[property];
      Backbone.Model.prototype[property] = function (...args) {
        Drupal.deprecationError({
          message: `Backbone.model.${property} is deprecated in drupal:9.4.0 and will be removed from drupal:10.0.0.`,
        });
        return originalFunction.apply(this, args);
      };
    }
  });

  Backbone.View.prototype = Drupal.deprecatedProperty({
    target: Backbone.View.prototype,
    deprecatedProperty: '$el',
    message: 'Backbone.View.$el is deprecated in drupal:9.4.0 and will be removed from drupal:10.0.0. Use DrupalView.el instead.',
  });
})(Backbone, Drupal);
