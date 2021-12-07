Drupal.toolbar = {
  models: {},
};

Drupal.toolbar.models.toolbarModel = {
  get(property) {
    switch (property) {
      case 'activeTab':
        console.log(
          `The get property for Toolbar is not supported beginning with Drupal 10. But ${property} can be accessed directly using Drupal.toolbar.models.toolbarModel.${property} instead.`,
        );
        return Drupal.toolbar.models.toolbarModel.activeTab();
      case 'orientation':
        console.log(
          `The get property for Toolbar is not supported beginning with Drupal 10. But ${property} can be accessed directly using Drupal.toolbar.models.toolbarModel.${property} instead.`,
        );
        return Drupal.toolbar.models.toolbarModel.orientation();
      default:
        console.log(
          `The get property for Toolbar is not supported beginning with Drupal 10. But ${property} can be accessed directly using Drupal.toolbar.models.toolbarModel.${property} instead.`,
        );
    }
  },
  // Use rest parameters to allow set be used with one or more arguments.
  set(a, ...theArgs) {
    // Check if there is more than one argument passed to the function
    if (theArgs.length) {
      if (a === 'activeTab') {
        console.log(
          `The set property for Toolbar is not supported beginning with Drupal 10. But ${a} can be set directly using Drupal.toolbar.models.toolbarModel.${a} instead.`,
        );
        Drupal.toolbar.toolbarBehaviors.activeTab = theArgs[0];
      }
    } else {
      // Only one argument is passed into the function
      const prop = Object.keys(a)[0];
      const value = a[prop];
      if (prop === 'activeTab') {
        console.log(
          `The set property for Toolbar is not supported beginning with Drupal 10. But ${prop} can be set directly using Drupal.toolbar.models.toolbarModel.${prop} instead.`,
        );
        Drupal.toolbar.toolbarBehaviors.activeTab = value;
      }
    }
  },
};

Drupal.toolbar.MenuModel = {
  subtrees: {},
};

// ??????
Drupal.toolbar.ToolbarVisualView = {
 // console.log('ToolbarVisualView is not supported beginning with Drupal 10.'),
  prototype: Drupal.toolbar.toolbarBehaviors,
};
