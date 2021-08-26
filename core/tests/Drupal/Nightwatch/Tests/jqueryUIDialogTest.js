// eslint-disable no-use-before-define
module.exports = {
  '@tags': ['core'],
  before(browser) {
    browser.drupalInstall().drupalLoginAsAdmin(() => {
      browser
        .drupalRelativeURL('/admin/modules')
        .setValue('input[type="search"]', 'jQuery Simulate')
        .waitForElementVisible(
          'input[name="modules[jquery_simulate][enable]"]',
          1000,
        )
        .click('input[name="modules[jquery_simulate][enable]"]')
        .click('input[type="submit"]')
        .drupalRelativeURL('/admin/modules')
        .setValue('input[type="search"]', 'jQuery UI Dialog Test')
        .waitForElementVisible(
          'input[name="modules[jqueryui_dialog_test][enable]"]',
          1000,
        )
        .click('input[name="modules[jqueryui_dialog_test][enable]"]')
        .click('input[type="submit"]');
    });
  },
  beforeEach(browser) {
    browser
      .drupalRelativeURL('/jqueryui-dialog-test')
      .waitForElementPresent('#dialog-container', 1000);
  },
  after(browser) {
    browser.drupalUninstall();
  },
  'markup structure': (browser) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $ = jQuery;
        const toReturn = {};
        const element = $('<div>').dialog({
          buttons: [
            {
              text: 'Ok',
              click: $.noop,
            },
          ],
        });
        const widget = element.dialog('widget');
        const titlebar = widget.find('.ui-dialog-titlebar');
        const title = titlebar.find('.ui-dialog-title');
        const close = titlebar.find('.ui-dialog-titlebar-close');
        const buttonpane = widget.find('.ui-dialog-buttonpane');
        const buttonset = widget.find('.ui-dialog-buttonset');
        const buttons = buttonset.find('.ui-button');
        toReturn.widgetHasClasses = widget.is(
          '.ui-dialog.ui-dialog-buttons.ui-widget.ui-widget-content',
        );
        toReturn.titleBarHasClasses = titlebar.is(
          '.ui-dialog-titlebar.ui-widget-header',
        );
        toReturn.oneTitlebar = titlebar.length === 1;
        toReturn.closeHasClasses = close.is(
          '.ui-dialog-titlebar-close.ui-widget',
        );
        toReturn.oneClose = close.length === 1;
        toReturn.oneTitle = title.length === 1;
        toReturn.elementHasClasses = element.is(
          '.ui-dialog-content.ui-widget-content',
        );
        toReturn.buttonPaneHasClasses = buttonpane.is(
          '.ui-dialog-buttonpane.ui-widget-content',
        );
        toReturn.oneButtonPane = buttonpane.length === 1;
        toReturn.oneButtonSet = buttonset.length === 1;
        toReturn.oneButtons = buttons.length === 1;

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          widgetHasClasses:
            'Widget has "ui-dialog ui-dialog-buttons ui-widget ui-widget-content" classes',
          titleBarHasClasses:
            'Title bar has "ui-dialog-titlebar ui-widget-header" classes',
          oneTitlebar: 'Dialog has exactly one titlebar',
          closeHasClasses:
            'Close has "ui-dialog-titlebar-close ui-widget" classes',
          oneClose: 'Titlebar has exactly one close button',
          oneTitle: 'Titlebar has exactly one title',
          elementHasClasses:
            'Element has "ui-dialog-content ui-widget-content" classes',
          buttonPaneHasClasses:
            'Buttonpane has "ui-dialog-buttonpane ui-widget-content" classes',
          oneButtonPane: 'Dialog has exactly one buttonpane',
          oneButtonSet: 'Buttonpane has exactly one buttonset',
          oneButtons: 'Buttonset contains exactly 1 button when created with 1',
        };
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
      },
    );
  },
  'markup structure - no buttons': (browser) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $ = jQuery;
        const toReturn = {};
        const element = $('<div>').dialog();
        const widget = element.dialog('widget');
        const titlebar = widget.find('.ui-dialog-titlebar');
        const title = titlebar.find('.ui-dialog-title');
        const close = titlebar.find('.ui-dialog-titlebar-close');
        toReturn.widgetHasClasses = widget.is(
          '.ui-dialog.ui-widget.ui-widget-content',
        );
        toReturn.titleBarHasClasses = titlebar.is(
          '.ui-dialog-titlebar.ui-widget-header',
        );
        toReturn.oneTitlebar = titlebar.length === 1;
        toReturn.closeHasClasses = close.is(
          '.ui-dialog-titlebar-close.ui-widget',
        );
        toReturn.oneClose = close.length === 1;
        toReturn.oneTitle = title.length === 1;
        toReturn.elementHasClasses = element.is(
          '.ui-dialog-content.ui-widget-content',
        );

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          widgetHasClasses:
            'Widget has "ui-dialog ui-widget ui-widget-content" classes',
          titleBarHasClasses:
            'Title bar has "ui-dialog-titlebar ui-widget-header" classes',
          oneTitlebar: 'Dialog has exactly one titlebar',
          closeHasClasses:
            'Close has "ui-dialog-titlebar-close ui-widget" classes',
          oneClose: 'Titlebar has exactly one close button',
          oneTitle: 'Titlebar has exactly one title',
          elementHasClasses:
            'Element has "ui-dialog-content ui-widget-content" classes',
        };
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
      },
    );
  },
  'title id': (browser) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $ = jQuery;
        const element = $('<div>').dialog();
        const titleId = element
          .dialog('widget')
          .find('.ui-dialog-title')
          .attr('id');

        return /ui-id-\d+$/.test(titleId);
      },
      [],
      (result) => {
        browser.assert.ok(result.value, 'auto-numbered title id');
      },
    );
  },
  aria: (browser) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $ = jQuery;
        const toReturn = {};
        let element = $('<div>').dialog();
        const wrapper = element.dialog('widget');
        toReturn.roleIsDialog = wrapper.attr('role') === 'dialog';
        toReturn.labelledById =
          wrapper.attr('aria-labelledby') ===
          wrapper.find('.ui-dialog-title').attr('id');
        toReturn.describedByAdded =
          wrapper.attr('aria-describedby') === element.attr('id');
        element.remove();

        element = $(
          "<div><div aria-describedby='section2'><p id='section2'>description</p></div></div>",
        ).dialog();
        toReturn.noNewDescriptionAdded =
          element.dialog('widget').attr('aria-describedby') == null;
        toReturn.debug = element.dialog('widget').attr('aria-describedby');
        element.remove();

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          roleIsDialog: 'dialog role',
          labelledById: 'labelledby matches ID',
          describedByAdded: 'aria-describedby added',
          noNewDescriptionAdded:
            'no aria-describedby added, as already present in markup',
        };
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
      },
    );
  },
  'widget method': (browser) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $ = jQuery;
        const dialog = $('<div>').appendTo('#dialog-container').dialog();
        return dialog.parent()[0].isEqualNode(dialog.dialog('widget')[0]);
      },
      [],
      (result) => {
        browser.assert.ok(result.value, 'widget returns parent');
      },
    );
  },
  'focus tabbable': (browser) => {
    browser.executeAsync(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        let element = {};

        let options = {
          buttons: [
            {
              text: 'Ok',
              click: $.noop,
            },
          ],
        };

        function checkFocus(markup, options, testFn, next) {
          element = $(markup).dialog(options);
          setTimeout(function () {
            testFn(function proceed() {
              element.remove();
              if (next === 'completed') {
                done(toReturn);
              } else {
                setTimeout(next);
              }
            });
          });
        }

        function step1() {
          checkFocus(
            '<div><input><input></div>',
            options,
            function (complete) {
              const input = element
                .find('input:last')
                .trigger('focus')
                .trigger('blur');
              setTimeout(function () {
                element.dialog('instance')._focusTabbable();
                toReturn.step1Previous = input[0].isEqualNode(
                  document.activeElement,
                );
                complete();
              });
            },
            step2,
          );
        }

        function step2() {
          checkFocus(
            '<div><input><input autofocus></div>',
            options,
            function (complete) {
              toReturn.step2FirstElementInside = element
                .find('input')[1]
                .isEqualNode(document.activeElement);
              complete();
            },
            step3,
          );
        }

        function step3() {
          checkFocus(
            '<div><input><input></div>',
            options,
            function (complete) {
              toReturn.step3InsideContentElement = element
                .find('input')[0]
                .isEqualNode(document.activeElement);
              complete();
            },
            step4,
          );
        }

        function step4() {
          checkFocus(
            '<div>text</div>',
            options,
            function (complete) {
              toReturn.step4inButtonpane = element
                .dialog('widget')
                .find('.ui-dialog-buttonpane button')[0]
                .isEqualNode(document.activeElement);
              complete();
            },
            step5,
          );
        }

        function step5() {
          checkFocus(
            '<div>text</div>',
            {},
            function (complete) {
              toReturn.step5CloseButton = element
                .dialog('widget')
                .find('.ui-dialog-titlebar .ui-dialog-titlebar-close')[0]
                .isEqualNode(document.activeElement);
              complete();
            },
            step6,
          );
        }

        function step6() {
          checkFocus(
            '<div>text</div>',
            { autoOpen: false },
            function (complete) {
              element.dialog('widget').find('.ui-dialog-titlebar-close').hide();
              element.dialog('open');
              setTimeout(function () {
                toReturn.step6TheDialogItself = element
                  .parent()[0]
                  .isEqualNode(document.activeElement);
                complete();
              });
            },
            step7,
          );
        }
        function step7() {
          checkFocus(
            '<div><input><input autofocus></div>',
            {
              open: function () {
                const inputs = $(this).find('input');
                inputs.last().on('keydown', function (event) {
                  event.preventDefault();
                  inputs.first().trigger('focus');
                });
              },
            },
            function (complete) {
              const inputs = element.find('input');
              toReturn.step7FocusStartsOnSecond = inputs[1].isEqualNode(
                document.activeElement,
              );
              inputs.last().simulate('keydown', { keyCode: $.ui.keyCode.TAB });
              setTimeout(function () {
                toReturn.step7HonorPreventDefault = inputs[0].isEqualNode(
                  document.activeElement,
                );
                complete();
              }, 50);
            },
            'completed',
          );
        }
        step1();
      },
      [],
      (result) => {
        const expectedTrue = {
          step1Previous: '1. an element that was focused previously.',
          step2FirstElementInside:
            '2. first element inside the dialog matching [autofocus]',
          step3InsideContentElement:
            '3. tabbable element inside the content element',
          step4inButtonpane: '4. tabbable element inside the buttonpane',
          step5CloseButton: '5. the close button',
          step6TheDialogItself: '6. the dialog itself',
          step7FocusStartsOnSecond: 'Focus starts on second input',
          step7HonorPreventDefault:
            'Honor preventDefault, allowing custom focus management',
        };
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
      },
    );
  },
  '#7960: resizable handles below modal overlays': (browser) => {
    browser.execute(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function () {
        const $ = jQuery;
        const resizable = $('<div>').resizable();
        const dialog = $('<div>').dialog({ modal: true });
        const resizableZindex = parseInt(
          resizable.find('.ui-resizable-handle').css('zIndex'),
          10,
        );
        const overlayZindex = parseInt(
          $('.ui-widget-overlay').css('zIndex'),
          10,
        );
        return resizableZindex < overlayZindex;
      },
      [],
      (result) => {
        browser.assert.ok(
          result.value,
          'Resizable handles have lower z-index than modal overlay',
        );
      },
    );
  },
  'Prevent tabbing out of dialogs': (browser) => {
    browser.executeAsync(
      // eslint-disable-next-line func-names, prefer-arrow-callback
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const element = $(
          "<div><input name='0'><input name='1'></div>",
        ).dialog();
        const inputs = element.find('input');

        // Remove close button to test focus on just the two buttons
        element.dialog('widget').find('.ui-button').remove();

        function checkTab() {
          toReturn.tabFocusInModal = inputs[0].isEqualNode(
            document.activeElement,
          );

          // Check shift tab
          $(document.activeElement).simulate('keydown', {
            keyCode: $.ui.keyCode.TAB,
            shiftKey: true,
          });
          setTimeout(checkShiftTab);
        }

        function checkShiftTab() {
          toReturn.shiftTabMoveFocusBack = inputs[1].isEqualNode(
            document.activeElement,
          );
          element.remove();
          setTimeout(done(toReturn));
        }

        inputs[1].focus();
        setTimeout(function () {
          toReturn.focusSetOnSecondInput = inputs[1].isEqualNode(
            document.activeElement,
          );
          inputs.eq(1).simulate('keydown', { keyCode: $.ui.keyCode.TAB });

          setTimeout(checkTab);
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          tabFocusInModal: 'Tab key event moved focus within the modal',
          shiftTabMoveFocusBack:
            'Shift-Tab key event moved focus back to second input',
          focusSetOnSecondInput: 'Focus set on second input',
        };
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
      },
    );
  },
};
