// TODO: need to uncomment this but rn toolbar doesn't work with it here
// ask how to add this 'models' when there is already an existing Drupal.toolbar in toolbar.es6
// Drupal.toolbar = {
//   models: {},
// };

Drupal.toolbar.models.toolbarModel = {
  get(property) {
    console.log(
      `The get function for Toolbar is not supported beginning with Drupal 10. But ${property} can be accessed directly using Drupal.toolbar.toolbarBehaviors.${property} instead.`,
    );
    return Drupal.toolbar.toolbarBehaviors[property];
  },
  // Use rest parameters to allow set be used with one or more arguments.
  set(...args) {
    if (typeof args[0] === 'object') {
      // Individually set each option specified in the object.
      Object.keys(args[0]).forEach((key) => {
        console.log(
          `The set function for Toolbar is not supported beginning with Drupal 10. But ${key} can be set directly using Drupal.toolbar.toolbarBehaviors.${key} instead.`,
        );
        Drupal.toolbar.toolbarBehaviors[key] = args[0][key];
      });
      // If there is a second argument
    } else if (args[1].length) {
      console.log(
        `The set function for Toolbar is not supported beginning with Drupal 10. But ${args[0]} can be set directly using Drupal.toolbar.toolbarBehaviors.${args[0]} instead.`,
      );
      Drupal.toolbar.toolbarBehaviors[args[0]] = args[1];
    }
  },
};

Drupal.toolbar.MenuModel = () => {
  console.log(
    'MenuModel for Toolbar is not supported beginning with Drupal 10. Instead use Drupal.toolbar.toolbarBehaviors',
  );

  return {
    ...Drupal.toolbar.toolbarBehaviors,
    set(...args) {
      if (typeof args[0] === 'object') {
        // Individually set each option specified in the object.
        Object.keys(args[0]).forEach((key) => {
          console.log(
            `The set property for Toolbar is not supported beginning with Drupal 10. But ${key} can be set directly using Drupal.toolbar.toolbarBehaviors.${key} instead.`,
          );
          Drupal.toolbar.toolbarBehaviors[key] = args[0][key];
        });
      } else if (args[1].length) {
        console.log(
          `The set property for Toolbar is not supported beginning with Drupal 10. But ${args[0]} can be set directly using Drupal.toolbar.toolbarBehaviors.${args[0]} instead.`,
        );
        Drupal.toolbar.toolbarBehaviors[args[0]] = args[1];
      }
    },
  };
};

// TODO: add shim for .prototype usage
