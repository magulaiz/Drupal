/**
 * @file
 * Backbone deprecations.
 */

(($, Drupal) => {
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
    'get',
    'escape',
    'has',
    'matches',
    'set',
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
      // Try to add deprecation messages to backbone functions.
      const originalFunction = Backbone.Model.prototype[property];
      // Backbone.Model.prototype[property] = function (...args) {
      //   if (property === 'get') {
      //     debugger;
      //   }
      //   console.log(`you called ${property}, dude`);
      //   return originalFunction.apply(...args);
      // };
    }
  });
})(Backbone, Drupal);
