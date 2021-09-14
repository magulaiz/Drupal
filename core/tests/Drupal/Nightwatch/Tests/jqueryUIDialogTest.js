/* eslint-disable no-use-before-define, func-names, prefer-arrow-callback, max-nested-callbacks */
// cSpell:ignore expando plusplus dialogbeforeclose Zindex dialogopen dialogfocus dialogdragstart dialogdrag dialogdragstop dialogresizestart dialogresize dialogresizestop dialogclose

const domEquals = function (selector, modifier, message) {
  function getElementStyles(elem) {
    const $ = jQuery;
    const styles = {};
    const style = elem.ownerDocument.defaultView
      ? elem.ownerDocument.defaultView.getComputedStyle(elem, null)
      : elem.currentStyle;
    let key = null;
    let len = null;

    if (style && style.length && style[0] && style[style[0]]) {
      len = style.length;
      // eslint-disable-next-line no-plusplus
      while (len--) {
        key = style[len];
        if (typeof style[key] === 'string') {
          styles[$.camelCase(key)] = style[key];
        }
      }

      // Support: Opera, IE <9
    } else {
      // eslint-disable-next-line no-restricted-syntax
      for (key in style) {
        if (typeof style[key] === 'string') {
          styles[key] = style[key];
        }
      }
    }

    return styles;
  }

  function extract(theSelector, theMessage) {
    const properties = ['disabled', 'readOnly'];
    const attributes = [
      'autocomplete',
      'aria-activedescendant',
      'aria-controls',
      'aria-describedby',
      'aria-disabled',
      'aria-expanded',
      'aria-haspopup',
      'aria-hidden',
      'aria-labelledby',
      'aria-pressed',
      'aria-selected',
      'aria-valuemax',
      'aria-valuemin',
      'aria-valuenow',
      'class',
      'href',
      'id',
      'nodeName',
      'role',
      'tabIndex',
      'title',
    ];
    const $ = jQuery;
    const elem = $(theSelector);
    if (!elem.length) {
      throw new Error(
        `domEqual failed, can't extract ${theSelector}, message was: ${theMessage}`,
      );
    }

    const result = {};
    let children = {};
    $.each(properties, function (index, attr) {
      const value = elem.prop(attr);
      result[attr] = value != null ? value : '';
    });
    $.each(attributes, function (index, attr) {
      const value = elem.attr(attr);
      result[attr] = value != null ? value : '';
    });
    result.style = getElementStyles(elem[0]);
    result.data = $.extend({}, elem.data());
    delete result.data[$.expando];
    children = elem.children();
    if (children.length) {
      result.children = elem
        .children()
        .map(function () {
          return extract($(this));
        })
        .get();
    } else {
      result.text = elem.text();
    }
    return result;
  }

  // Get current state prior to modifier
  const expected = extract(selector, message);

  function done() {
    const actual = extract(selector, message);
    return [actual, expected];
  }

  // Run modifier (async or sync), then compare state via done()
  if (modifier.length) {
    return modifier(done);
  }
  modifier();
  return done();
};

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
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        let element = {};

        const options = {
          buttons: [
            {
              text: 'Ok',
              click: $.noop,
            },
          ],
        };

        function checkFocus(markup, dialogOptions, testFn, next) {
          element = $(markup).dialog(dialogOptions);
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
              open() {
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
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
      function () {
        const $ = jQuery;
        const resizable = $('<div>').resizable();
        $('<div>').dialog({ modal: true });
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
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  '#9048: multiple modal dialogs opened and closed in different order': (
    browser,
  ) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        $('#dialog1, #dialog2').dialog({ autoOpen: false, modal: true });
        $('#dialog1').dialog('open');
        $('#dialog2').dialog('open');
        $('#dialog1').dialog('close');
        setTimeout(function () {
          $('#dialog2').dialog('close');
          $('#favorite-animal').trigger('focus');
          done(true);
        });
      },
      [],
      (result) => {
        browser.assert.ok(
          result,
          'event handlers cleaned up (no errors thrown)',
        );
      },
    );
  },
  'interaction between overlay and other dialogs': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        $.widget('ui.testWidget', $.ui.dialog, {
          options: {
            modal: true,
            autoOpen: false,
          },
        });

        const first = $("<div><input id='input-1'></div>").dialog({
          modal: true,
        });
        const firstInput = first.find('input');
        const second = $("<div><input id='input-2'></div>").testWidget();
        const secondInput = second.find('input');

        // Wait for the modal to init
        setTimeout(function () {
          second.testWidget('open');

          // Simulate user tabbing from address bar to an element outside the dialog
          $('#favorite-animal').trigger('focus');
          setTimeout(function () {
            toReturn.secondInputFocused = secondInput[0].isEqualNode(
              document.activeElement,
            );

            // Last active dialog must receive focus
            firstInput.trigger('focus');
            $('#favorite-animal').trigger('focus');
            setTimeout(function () {
              toReturn.firstInputFocused = firstInput[0].isEqualNode(
                document.activeElement,
              );

              // Cleanup
              first.remove();
              second.remove();
              delete $.ui.testWidget;
              delete $.fn.testWidget;
              done(toReturn);
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          secondInputFocused: 'Second input focused',
          firstInputFocused: 'Last active dialog input focused',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  open: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const element = $('<div></div>');
        element.dialog({
          open(ev, ui) {
            toReturn.internalOpenFlagSet = element.dialog('instance')._isOpen;
            toReturn.autoOpenFiresCallback = true;
            toReturn.contextOfCallback = element[0].isEqualNode(this);
            toReturn.eventTypeInCallback = ev.type === 'dialogopen';
            toReturn.uiHashInCallback = JSON.stringify(ui) === '{}';
          },
        });
        element.remove();

        const element2 = $('<div></div>');
        element2
          .dialog({
            autoOpen: false,
            open(ev, ui) {
              toReturn.contextOfCallback2 = element2[0].isEqualNode(this);
              toReturn.eventTypeInCallback2 = ev.type === 'dialogopen';
              toReturn.uiHashInCallback2 = JSON.stringify(ui) === '{}';
              toReturn.firesOpenCallback = true;
            },
          })
          .on('dialogopen', function (ev, ui) {
            toReturn.internalIsOpenInEvent =
              element2.dialog('instance')._isOpen;
            toReturn.dialogOpenFiresOpenEvent = true;
            toReturn.contextOfCallbackEvent = element2[0].isEqualNode(this);
            toReturn.uiHashInCallbackEvent = JSON.stringify(ui) === '{}';
            done(toReturn);
          });
        element2.dialog('open');
      },
      [],
      (result) => {
        const expectedTrue = {
          internalOpenFlagSet: 'internal _isOpen flag is set',
          autoOpenFiresCallback: 'autoOpen: true fires open callback',
          contextOfCallback: 'context of callback',
          eventTypeInCallback: 'event type in callback',
          uiHashInCallback: 'ui hash in callback',
          firesOpenCallback: ".dialog('open') fires open callback",
          contextOfCallback2: 'context of callback 2',
          eventTypeInCallback2: 'event type in callback 2',
          uiHashInCallback2: 'ui hash in callback 2',
          internalIsOpenInEvent: 'internal _isOpen flag is set in event',
          dialogOpenFiresOpenEvent: "dialog('open') fires open event",
          contextOfCallbackEvent: 'context of callback in event',
          uiHashInCallbackEvent: 'ui hash in event',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  focus: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        toReturn.noRepeatEvents = true;
        const element = $('#dialog1').dialog({
          autoOpen: false,
        });
        const other = $('#dialog2').dialog({
          autoOpen: false,
        });

        element.one('dialogopen', function () {
          if (toReturn.hasOwnProperty('openJustOnce')) {
            toReturn.noRepeatEvents = 'repeated openJustOnce';
          }
          toReturn.openJustOnce = true;
        });
        element.one('dialogfocus', function () {
          if (toReturn.hasOwnProperty('focusOnOpen')) {
            toReturn.noRepeatEvents = 'repeated focusOnOpen';
          }
          toReturn.focusOnOpen = true;
        });
        other.dialog('open');

        element.one('dialogfocus', function () {
          if (toReturn.hasOwnProperty('whenOpeningNotOnTop')) {
            toReturn.noRepeatEvents = 'repeated whenOpeningNotOnTop';
          }
          toReturn.whenOpeningNotOnTop = true;
        });
        other.dialog('open');
        element.dialog('open');

        element.one('dialogfocus', function () {
          if (toReturn.hasOwnProperty('moveToTopButNotOnTop')) {
            toReturn.noRepeatEvents = 'repeated moveToTopButNotOnTop';
          }
          toReturn.moveToTopButNotOnTop = true;
        });
        other.dialog('moveToTop');
        element.dialog('moveToTop');

        element.on('dialogfocus', function () {
          if (toReturn.hasOwnProperty('mouseDownNotOnTop')) {
            toReturn.noRepeatEvents = 'repeated mouseDownNotOnTop';
          }
          toReturn.mouseDownNotOnTop = true;
        });
        other.dialog('moveToTop');
        element.trigger('mousedown');

        // Triggers just once when already on top
        element.dialog('open');
        element.dialog('moveToTop');
        element.trigger('mousedown');
        setTimeout(() => {
          done(toReturn);
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          openJustOnce: 'open, just once',
          focusOnOpen: 'focus on open',
          whenOpeningNotOnTop:
            "when opening and already open and wasn't on top",
          moveToTopButNotOnTop: "when calling moveToTop and wasn't on top",
          mouseDownNotOnTop:
            "when mousedown anywhere on the dialog and it wasn't on top",
          noRepeatEvents:
            result.value.noRepeatEvents === true
              ? 'no repeat events'
              : `${result.noRepeatEvents} and should not have`,
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  dragStart: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const drag = function (element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        };

        const element = $('<div></div>')
          .dialog({
            dragStart(ev, ui) {
              toReturn.draggingFiresDragStartCallback = true;
              toReturn.contextOfCallback = element[0].isEqualNode(this);
              toReturn.eventTypeInCallback = ev.type === 'dialogdragstart';
              toReturn.uiPositionInCallback = ui.position !== undefined;
              toReturn.uiOffsetInCallback = ui.offset !== undefined;
            },
          })
          .on('dialogdragstart', function (ev, ui) {
            toReturn.draggingFiresDragStartEvent = true;
            toReturn.contextOfEvent = element[0].isEqualNode(this);
            toReturn.uiPositionInEvent = ui.position !== undefined;
            toReturn.uiOffsetInEvent = ui.offset !== undefined;
            done(toReturn);
          });

        const handle = $('.ui-dialog-titlebar', element.dialog('widget'));
        drag(element, handle, 50, 50);
      },
      [],
      (result) => {
        const expectedTrue = {
          draggingFiresDragStartCallback: 'dragging fires dragStart callback',
          contextOfCallback: 'context of callback',
          eventTypeInCallback: 'event type in callback',
          uiPositionInCallback: 'ui.position in callback',
          uiOffsetInCallback: 'ui.offset in callback',
          draggingFiresDragStartEvent: 'dragging fires dialogdragstart event',
          contextOfEvent: 'context of event',
          uiPositionInEvent: 'ui.position in event',
          uiOffsetInEvent: 'ui.offset in event',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  drag: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        let hasDragged = false;
        const drag = function (element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        };

        const element = $('<div></div>')
          .dialog({
            drag(ev, ui) {
              if (!hasDragged) {
                toReturn.draggingFiresDragCallback = true;
                toReturn.contextOfCallback = element[0].isEqualNode(this);
                toReturn.eventTypeInCallback = ev.type === 'dialogdrag';
                toReturn.uiPositionInCallback = ui.position !== undefined;
                toReturn.uiOffsetInCallback = ui.offset !== undefined;
                hasDragged = true;
              }
            },
          })
          .one('dialogdrag', function (ev, ui) {
            toReturn.draggingFiresDialogDragEvent = true;
            toReturn.contextOfEvent = element[0].isEqualNode(this);
            toReturn.uiPositionInEvent = ui.position !== undefined;
            toReturn.uiOffsetInEvent = ui.offset !== undefined;
            done(toReturn);
          });

        const handle = $('.ui-dialog-titlebar', element.dialog('widget'));
        drag(element, handle, 50, 50);
      },
      [],
      (result) => {
        const expectedTrue = {
          draggingFiresDragCallback: 'dragging fires drag callback',
          contextOfCallback: 'context of callback',
          eventTypeInCallback: 'event type in callback',
          uiPositionInCallback: 'ui.position in callback',
          uiOffsetInCallback: 'ui.offset in callback',
          draggingFiresDialogDragEvent: 'dragging fires dialogdrag event',
          contextOfEvent: 'context of event',
          uiPositionInEvent: 'ui.position in event',
          uiOffsetInEvent: 'ui.offset in event',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  dragStop: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const drag = function (element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        };

        const element = $('<div></div>')
          .dialog({
            dragStop(ev, ui) {
              toReturn.draggingFiresDragStopCallback = true;
              toReturn.contextOfCallback = element[0].isEqualNode(this);
              toReturn.eventTypeInCallback = ev.type === 'dialogdragstop';
              toReturn.uiPositionInCallback = ui.position !== undefined;
              toReturn.uiOffsetInCallback = ui.offset !== undefined;
            },
          })
          .on('dialogdragstop', function (ev, ui) {
            toReturn.draggingFiresDialogDragStopEvent = true;
            toReturn.contextOfEvent = element[0].isEqualNode(this);
            toReturn.uiPositionInEvent = ui.position !== undefined;
            toReturn.uiOffsetInEvent = ui.offset !== undefined;
            done(toReturn);
          });

        const handle = $('.ui-dialog-titlebar', element.dialog('widget'));
        drag(element, handle, 50, 50);
      },
      [],
      (result) => {
        const expectedTrue = {
          draggingFiresDragStopCallback: 'dragging fires dragStop callback',
          contextOfCallback: 'context of callback',
          eventTypeInCallback: 'event type in callback',
          uiPositionInCallback: 'ui.position in callback',
          uiOffsetInCallback: 'ui.offset in callback',
          draggingFiresDialogDragStopEvent:
            'dragging fires dialogdragstop event',
          contextOfEvent: 'context of event',
          uiPositionInEvent: 'ui.position in event',
          uiOffsetInEvent: 'ui.offset in event',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  resizeStart: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const drag = function (element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        };

        const element = $('<div></div>')
          .dialog({
            resizeStart(ev, ui) {
              toReturn.draggingFiresResizeStartCallback = true;
              toReturn.contextOfCallback = element[0].isEqualNode(this);
              toReturn.eventTypeInCallback = ev.type === 'dialogresizestart';
              toReturn.uiOriginalPositionInCallback =
                ui.originalPosition !== undefined;
              toReturn.uiOriginalSizeInCallback = ui.originalSize !== undefined;
              toReturn.uiPositionInCallback = ui.position !== undefined;
              toReturn.uiSizeInCallback = ui.size !== undefined;
            },
          })
          .on('dialogresizestart', function (ev, ui) {
            toReturn.draggingFiresDialogResizeStartEvent = true;
            toReturn.contextOfEvent = element[0].isEqualNode(this);
            toReturn.uiOriginalPositionInEvent =
              ui.originalPosition !== undefined;
            toReturn.uiOriginalSizeInEvent = ui.originalSize !== undefined;
            toReturn.uiPositionInEvent = ui.position !== undefined;
            toReturn.uiSizeInEvent = ui.size !== undefined;
            done(toReturn);
          });

        const handle = $('.ui-resizable-se', element.dialog('widget'));
        drag(element, handle, 50, 50);
      },
      [],
      (result) => {
        const expectedTrue = {
          draggingFiresResizeStartCallback:
            'dragging fires resizeStart callback',
          contextOfCallback: 'context of callback',
          eventTypeInCallback: 'event type in callback',
          uiOriginalPositionInCallback: 'original ui.position in callback',
          uiOriginalSizeInCallback: 'original ui.size in callback',
          uiPositionInCallback: 'ui.position in callback',
          uiSizeInCallback: 'ui.size in callback',
          draggingFiresDialogResizeStartEvent:
            'dragging fires dialogresizestart event',
          contextOfEvent: 'context of event',
          uiOriginalPositionInEvent: 'original ui.position in event',
          uiOriginalSizeInEvent: 'original ui.size in event',
          uiPositionInEvent: 'ui.position in event',
          uiSizeInEvent: 'ui.size in event',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  resize: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const drag = function (element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        };
        let hasResized = false;
        const element = $('<div></div>')
          .dialog({
            resize(ev, ui) {
              if (!hasResized) {
                toReturn.draggingFiresResizeCallback = true;
                toReturn.contextOfCallback = element[0].isEqualNode(this);
                toReturn.eventTypeInCallback = ev.type === 'dialogresize';
                toReturn.uiOriginalPositionInCallback =
                  ui.originalPosition !== undefined;
                toReturn.uiOriginalSizeInCallback =
                  ui.originalSize !== undefined;
                toReturn.uiPositionInCallback = ui.position !== undefined;
                toReturn.uiSizeInCallback = ui.size !== undefined;
                hasResized = true;
              }
            },
          })
          .on('dialogresize', function (ev, ui) {
            toReturn.draggingFiresDialogResizeEvent = true;
            toReturn.contextOfEvent = element[0].isEqualNode(this);
            toReturn.uiOriginalPositionInEvent =
              ui.originalPosition !== undefined;
            toReturn.uiOriginalSizeInEvent = ui.originalSize !== undefined;
            toReturn.uiPositionInEvent = ui.position !== undefined;
            toReturn.uiSizeInEvent = ui.size !== undefined;
            done(toReturn);
          });

        const handle = $('.ui-resizable-se', element.dialog('widget'));
        drag(element, handle, 50, 50);
      },
      [],
      (result) => {
        const expectedTrue = {
          draggingFiresResizeCallback: 'dragging fires resize callback',
          contextOfCallback: 'context of callback',
          eventTypeInCallback: 'event type in callback',
          uiOriginalPositionInCallback: 'original ui.position in callback',
          uiOriginalSizeInCallback: 'original ui.size in callback',
          uiPositionInCallback: 'ui.position in callback',
          uiSizeInCallback: 'ui.size in callback',
          draggingFiresDialogResizeEvent: 'dragging fires dialogresize event',
          contextOfEvent: 'context of event',
          uiOriginalPositionInEvent: 'original ui.position in event',
          uiOriginalSizeInEvent: 'original ui.size in event',
          uiPositionInEvent: 'ui.position in event',
          uiSizeInEvent: 'ui.size in event',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  resizeStop: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const drag = function (element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        };

        const element = $('<div></div>')
          .dialog({
            resizeStop(ev, ui) {
              toReturn.draggingFiresResizeStopCallback = true;
              toReturn.contextOfCallback = element[0].isEqualNode(this);
              toReturn.eventTypeInCallback = ev.type === 'dialogresizestop';
              toReturn.uiOriginalPositionInCallback =
                ui.originalPosition !== undefined;
              toReturn.uiOriginalSizeInCallback = ui.originalSize !== undefined;
              toReturn.uiPositionInCallback = ui.position !== undefined;
              toReturn.uiSizeInCallback = ui.size !== undefined;
            },
          })
          .on('dialogresizestop', function (ev, ui) {
            toReturn.draggingFiresDialogResizeStopEvent = true;
            toReturn.contextOfEvent = element[0].isEqualNode(this);
            toReturn.uiOriginalPositionInEvent =
              ui.originalPosition !== undefined;
            toReturn.uiOriginalSizeInEvent = ui.originalSize !== undefined;
            toReturn.uiPositionInEvent = ui.position !== undefined;
            toReturn.uiSizeInEvent = ui.size !== undefined;
            done(toReturn);
          });

        const handle = $('.ui-resizable-se', element.dialog('widget'));
        drag(element, handle, 50, 50);
      },
      [],
      (result) => {
        const expectedTrue = {
          draggingFiresResizeStopCallback: 'dragging fires resizeStop callback',
          contextOfCallback: 'context of callback',
          eventTypeInCallback: 'event type in callback',
          uiOriginalPositionInCallback: 'original ui.position in callback',
          uiOriginalSizeInCallback: 'original ui.size in callback',
          uiPositionInCallback: 'ui.position in callback',
          uiSizeInCallback: 'ui.size in callback',
          draggingFiresDialogResizeStopEvent:
            'dragging fires dialogresizestop event',
          contextOfEvent: 'context of event',
          uiOriginalPositionInEvent: 'original ui.position in event',
          uiOriginalSizeInEvent: 'original ui.size in event',
          uiPositionInEvent: 'ui.position in event',
          uiSizeInEvent: 'ui.size in event',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  close: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const element = $('<div></div>')
          .dialog({
            close(ev, ui) {
              toReturn.dialogCloseFiresCloseCallback = true;
              toReturn.contextOfCallback = element[0].isEqualNode(this);
              toReturn.eventTypeInCallback = ev.type === 'dialogclose';
              toReturn.uiHashInCallback = JSON.stringify(ui) === '{}';
            },
          })
          .on('dialogclose', function (ev, ui) {
            toReturn.dialogCloseFiresDialogCloseEvent = true;
            toReturn.contextOfEvent = element[0].isEqualNode(this);
            toReturn.uiHashInEvent = JSON.stringify(ui) === '{}';
          });
        element.dialog('close');
        element.remove();

        // Close event with an effect.
        const element2 = $('<div></div>')
          .dialog({
            hide: 10,
            close(ev, ui) {
              toReturn.dialogCloseFiresCloseCallbackHasEffect = true;
              toReturn.contextOfCallbackHasEffect =
                element2[0].isEqualNode(this);
              toReturn.eventTypeInCallbackHasEffect = ev.type === 'dialogclose';
              toReturn.uiHashInCallbackHasEffect = JSON.stringify(ui) === '{}';
              done(toReturn);
            },
          })
          .on('dialogclose', function (ev, ui) {
            toReturn.dialogCloseFiresDialogCloseEventHasEffect = true;
            toReturn.contextOfEventHasEffect = element2[0].isEqualNode(this);
            toReturn.uiHashInEventHasEffect = JSON.stringify(ui) === '{}';
          });
        element2.dialog('close');
      },
      [],
      (result) => {
        const expectedTrue = {
          dialogCloseFiresCloseCallback:
            ".dialog('close') fires close callback",
          contextOfCallback: 'context of callback',
          eventTypeInCallback: 'event type in callback',
          uiHashInCallback: 'ui hash in callback',
          dialogCloseFiresDialogCloseEvent:
            ".dialog('close') fires dialogclose event",
          contextOfEvent: 'context of event',
          uiHashInEvent: 'ui hash in event',
          dialogCloseFiresCloseCallbackHasEffect:
            ".dialog('close') fires close callback",
          contextOfCallbackHasEffect: 'context of callback',
          eventTypeInCallbackHasEffect: 'event type in callback',
          uiHashInCallbackHasEffect: 'ui hash in callback',
          dialogCloseFiresDialogCloseEventHasEffect:
            ".dialog('close') fires dialogclose event",
          contextOfEventHasEffect: 'context of event',
          uiHashInEventHasEffect: 'ui hash in event',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  beforeClose: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        let element = {};
        element = $('<div></div>').dialog({
          beforeClose(ev, ui) {
            toReturn.dialogCloseFiresBeforeCloseCallback = true;
            toReturn.contextOfCallback = element[0].isEqualNode(this);
            toReturn.eventTypeInCallback = ev.type === 'dialogbeforeclose';
            toReturn.uiHashInCallback = JSON.stringify(ui) === '{}';
            return false;
          },
        });
        element.dialog('close');
        setTimeout(() => {
          toReturn.beforeCloseShouldPreventDialogClose = element
            .dialog('widget')
            .is(':visible');
          element.remove();

          element = $('<div></div>').dialog();
          element.dialog('option', 'beforeClose', function (ev, ui) {
            toReturn.dialogCloseFiresBeforeCloseCallbackAsOption = true;
            toReturn.contextOfCallbackAsOption = element[0].isEqualNode(this);
            toReturn.eventTypeInCallbackAsOption =
              ev.type === 'dialogbeforeclose';
            toReturn.uiHashInCallbackAsOption = JSON.stringify(ui) === '{}';
            return false;
          });
          element.dialog('close');

          setTimeout(() => {
            toReturn.beforeCloseAsOptionShouldPreventDialogClose = element
              .dialog('widget')
              .is(':visible');
            element.remove();

            element = $('<div></div>')
              .dialog()
              .on('dialogbeforeclose', function (ev, ui) {
                toReturn.dialogCloseTriggersDialogBeforeCloseEvent = true;
                toReturn.contextOfCallbackEvent = element[0].isEqualNode(this);
                toReturn.uiHashInCallbackEvent = JSON.stringify(ui) === '{}';
                toReturn.uiHashInEvent = JSON.stringify(ui) === '{}';
                return false;
              });
            element.dialog('close');
            setTimeout(() => {
              toReturn.dialogBeforeCloseEventPreventDialogClosing = element
                .dialog('widget')
                .is(':visible');
              done(toReturn);
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          dialogCloseFiresBeforeCloseCallback:
            ".dialog('close') fires beforeclose callback",
          contextOfCallback: 'context of callback',
          eventTypeInCallback: 'event type in callback',
          uiHashInCallback: 'ui hash in callback',
          uiHashInCallbackEvent: 'ui hash in callback event',
          beforeCloseShouldPreventDialogClose:
            'beforeClose callback should prevent dialog from closing',
          dialogCloseFiresBeforeCloseCallbackAsOption:
            ".dialog('close') fires beforeClose callback as option",
          contextOfCallbackAsOption: 'context of callback as option',
          eventTypeInCallbackAsOption: 'event type in callback as option',
          uiHashInCallbackAsOption: 'ui hash in callback as option',
          beforeCloseAsOptionShouldPreventDialogClose:
            'beforeClose callback as option should prevent dialog from closing',
          dialogCloseTriggersDialogBeforeCloseEvent:
            ".dialog('close') triggers dialogbeforeclose event",
          contextOfCallbackEvent: 'context of event',
          uiHashInEvent: 'ui hash in event',
          dialogBeforeCloseEventPreventDialogClosing:
            'dialogbeforeclose event should prevent dialog from closing',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  'ensure dialog container does not scroll on resize and focus': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const element = $('#dialog1').dialog();
        const initialScroll = $(window).scrollTop();
        element.dialog('option', 'height', 600);
        toReturn.scrollNotChangeAfterHeightChange =
          $(window).scrollTop() === initialScroll;
        setTimeout(function () {
          $('.ui-dialog-titlebar-close').simulate('mousedown');
          toReturn.scrollNotMoveAfterFocusMoveToDialog =
            $(window).scrollTop() === initialScroll;
          element.dialog('destroy');
          done(toReturn);
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          scrollNotChangeAfterHeightChange:
            "scroll hasn't moved after height change",
          scrollNotMoveAfterFocusMoveToDialog:
            "scroll hasn't moved after focus moved to dialog",
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  '#5184: isOpen in dialogclose event is true': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const element = $('<div></div>').dialog({
          close() {
            toReturn.dialogNotOpenDuringClose = !element.dialog('isOpen');
          },
        });
        setTimeout(() => {
          toReturn.dialogOpenAfterInit = element.dialog('isOpen');
          element.dialog('close');
          setTimeout(() => {
            toReturn.dialogNotOpenAfterClose = !element.dialog('isOpen');
            done(toReturn);
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          dialogNotOpenDuringClose: 'dialog is not open during close',
          dialogOpenAfterInit: 'dialog is open after init',
          dialogNotOpenAfterClose: 'dialog is not open after close',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  'ensure dialog keeps focus when clicking modal overlay': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        $('<div></div>').dialog({
          modal: true,
        });
        toReturn.focusInDialog =
          $(document.activeElement).closest('.ui-dialog').length === 1;
        $('.ui-widget-overlay').simulate('mousedown');
        setTimeout(() => {
          toReturn.focusStillInDialog =
            $(document.activeElement).closest('.ui-dialog').length === 1;
          done(toReturn);
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          focusInDialog: 'focus in dialog',
          focusStillInDialog: 'focus still in dialog',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  init: (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};
        $('<div></div>').appendTo('body').dialog().remove();
        toReturn.dialogCalledOnElement = true;

        $([]).dialog().remove();
        toReturn.dialogCalledOnEmptyCollection = true;

        $('<div></div>').dialog().remove();
        toReturn.dialogCalledOnDisconnectedDOM = true;

        $('<div></div>').appendTo('body').remove().dialog().remove();
        toReturn.dialogCalledOnDisconnectedRemovedDOM = true;

        const element = $('<div></div>').dialog();
        element.dialog('option', 'foo');
        element.remove();
        toReturn.arbitraryOptionGetterAfterInit = true;

        $('<div></div>').dialog().dialog('option', 'foo', 'bar').remove();
        toReturn.arbitraryOptionSetterAfterInit = true;
        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          dialogCalledOnElement: '.dialog() called on element',
          dialogCalledOnEmptyCollection: '.dialog() called on empty collection',
          dialogCalledOnDisconnectedDOM:
            '.dialog() called on disconnected DOMElement - never connected',
          dialogCalledOnDisconnectedRemovedDOM:
            '.dialog() called on disconnected DOMElement - removed',
          arbitraryOptionGetterAfterInit: 'arbitrary option getter after init',
          arbitraryOptionSetterAfterInit: 'arbitrary option setter after init',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  destroy: (browser) => {
    browser.execute(
      function (domEqualString) {
        const $ = jQuery;
        const toReturn = {};
        toReturn.mustMatch = {};
        // eslint-disable-next-line no-new-func
        const domEqual = new Function(`return ${domEqualString}`)();

        $('#dialog1, #form-dialog').hide();
        toReturn.mustMatch.dialog1 = domEqual('#dialog1', function () {
          const dialog = $('#dialog1').dialog().dialog('destroy');
          if (!dialog.parent()[0].isEqualNode($('#dialog-container')[0])) {
            throw new Error('Dialog parent is not dialog container');
          }
          if (dialog.index() !== 0) {
            throw new Error('Dialog index is not 0');
          }
        });

        toReturn.mustMatch.formDialog = domEqual('#form-dialog', function () {
          const dialog = $('#form-dialog').dialog().dialog('destroy');
          if (!dialog.parent()[0].isEqualNode($('#dialog-container')[0])) {
            throw new Error('Dialog parent is not dialog container');
          }
          if (dialog.index() !== 2) {
            throw new Error('Dialog index is not 2');
          }
        });

        // Ensure dimensions are restored (#8119).
        $('#dialog1').show().css({
          width: '400px',
          minHeight: '100px',
          height: '200px',
        });
        toReturn.mustMatch.dimensionsRestores = domEqual(
          '#dialog1',
          function () {
            $('#dialog1').dialog().dialog('destroy');
          },
        );

        // Don't throw errors when destroying a never opened modal dialog (#9004)
        $('#dialog1')
          .dialog({ autoOpen: false, modal: true })
          .dialog('destroy');
        toReturn.overlayDoesNotExist = $('.ui-widget-overlay').length === 0;
        toReturn.dialogOverlaysEqualsNumberOpen =
          typeof $(document).data('ui-dialog-overlays') === 'undefined';

        const element = $('#dialog1').dialog({ modal: true });
        const element2 = $('#dialog2').dialog({ modal: true });
        toReturn.overlaysCreatedWhenDialogsAreOpen =
          $('.ui-widget-overlay').length === 2;
        toReturn.dialogOverlayesEqualsNumberOfOpenOverlays =
          $(document).data('ui-dialog-overlays') === 2;
        element.dialog('close');
        toReturn.overlayRemainsAfterClosingOneDialog =
          $('.ui-widget-overlay').length === 1;
        toReturn.uiDialogOverlaysEqualsNumberOpenOverlays =
          $(document).data('ui-dialog-overlays') === 1;
        element.dialog('destroy');
        toReturn.overlayRemainsAfterDestroyingOneDialog =
          $('.ui-widget-overlay').length === 1;
        toReturn.uiDialogOverlaysEqualsNumberOpenOverlaysAfterDestroy =
          $(document).data('ui-dialog-overlays') === 1;
        element2.dialog('destroy');
        toReturn.overlaysRemoveWhenAllDialogsDestroyed =
          $('.ui-widget-overlay').length === 0;
        toReturn.uiDialogOverlaysEqualsNumberOpenOverlaysAfterAllGone =
          typeof $(document).data('ui-dialog-overlays') === 'undefined';

        return toReturn;
      },
      [domEquals.toString()],
      (result) => {
        const { mustMatch } = result.value;
        delete result.value.mustMatch;
        const expectedTrue = {
          overlayDoesNotExist: 'overlay does not exist',
          dialogOverlaysEqualsNumberOpen:
            'ui-dialog-overlays equals the number of open overlays',
          overlaysCreatedWhenDialogsAreOpen:
            'overlays created when dialogs are open',
          dialogOverlayesEqualsNumberOfOpenOverlays:
            'ui-dialog-overlays equals the number of open overlays',
          overlayRemainsAfterClosingOneDialog:
            'overlay remains after closing one dialog',
          uiDialogOverlaysEqualsNumberOpenOverlays:
            'ui-dialog-overlays equals the number of open overlays',
          overlayRemainsAfterDestroyingOneDialog:
            'overlay remains after destroying one dialog',
          uiDialogOverlaysEqualsNumberOpenOverlaysAfterDestroy:
            'ui-dialog-overlays equals the number of open overlays',
          overlaysRemoveWhenAllDialogsDestroyed:
            'overlays removed when all dialogs are destroyed',
          uiDialogOverlaysEqualsNumberOpenOverlaysAfterAllGone:
            'ui-dialog-overlays equals the number of open overlays',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
        browser.assert.equal(Object.keys(mustMatch).length, 3);
        Object.keys(mustMatch).forEach((property) => {
          browser.assert.deepEqual(
            mustMatch[property][0],
            mustMatch[property][1],
            `${property} are equivalent`,
          );
        });
      },
    );
  },
  '#9000: Dialog leaves broken event handler after close/destroy in certain cases':
    (browser) => {
      browser.executeAsync(
        function (done) {
          const $ = jQuery;
          $('#dialog1')
            .dialog({ modal: true })
            .dialog('close')
            .dialog('destroy');
          setTimeout(function () {
            $('#favorite-animal').trigger('focus');
            done(true);
          });
        },
        [],
        (result) => {
          browser.assert.equal(
            result.value,
            true,
            'close and destroy modal dialog before its really opened',
          );
        },
      );
    },
  '#4980: Destroy should place element back in original DOM position': (
    browser,
  ) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};
        const container = $(
          "<div id='container'><div id='modal'>Content</div></div>",
        );
        const modal = container.find('#modal');
        modal.dialog();
        toReturn.dialogShouldMoveModalToOutsideContainer = !$.contains(
          container[0],
          modal[0],
        );
        modal.dialog('destroy');
        toReturn.dialogShouldPlaceElementBackInOriginalDom = $.contains(
          container[0],
          modal[0],
        );
        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          dialogShouldMoveModalToOutsideContainer:
            'dialog should move modal element to outside container element',
          dialogShouldPlaceElementBackInOriginalDom:
            'dialog should place element back into dom',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  'enable/disable disabled': (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};
        const element = $('<div></div>').dialog();
        element.dialog('disable');
        toReturn.disableDoesNotDoAnything = !element.dialog(
          'option',
          'disabled',
        );
        toReturn.disableDoesNotAddClasses =
          !element.hasClass('ui-dialog-disabled') &&
          !element.hasClass('ui-state-disabled');
        toReturn.disableDoesNotAddAriaDisabled = !element
          .dialog('widget')
          .attr('aria-disabled');
        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          disableDoesNotDoAnything: "disable method doesn't do anything",
          disableDoesNotAddClasses: "disable method doesn't add classes",
          disableDoesNotAddAriaDisabled:
            "disable method doesn't add aria-disabled",
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  closeMethod: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const expected = $('<div></div>').dialog();
        const actual = expected.dialog('close');
        toReturn.closeIsChainable = actual === expected;

        const element = $('<div></div>').dialog();
        toReturn.dialogVisibleBeforeClose =
          element.dialog('widget').is(':visible') &&
          !element.dialog('widget').is(':hidden');
        element.dialog('close');
        setTimeout(() => {
          toReturn.dialogHiddenAfterClose =
            element.dialog('widget').is(':hidden') &&
            !element.dialog('widget').is(':visible');
          done(toReturn);
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          closeIsChainable: 'close is chainable',
          dialogVisibleBeforeClose: 'dialog visible before close method called',
          dialogHiddenAfterClose: 'dialog hidden after close method called',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  isOpen: (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};
        let element = {};
        element = $('<div></div>').dialog();
        toReturn.dialogOpenAfterInit = element.dialog('isOpen');
        element.dialog('close');
        toReturn.dialogIsClosed = !element.dialog('isOpen');
        element.remove();
        element = $('<div></div>').dialog({ autoOpen: false });
        toReturn.autoDialogCloseAfterInit = !element.dialog('isOpen');
        element.dialog('open');
        toReturn.autoDialogOpen = element.dialog('isOpen');
        element.remove();
        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          dialogOpenAfterInit: 'dialog is open after init',
          dialogIsClosed: 'dialog is closed',
          autoDialogCloseAfterInit: 'autoOpen dialog is open after init',
          autoDialogOpen: 'autoOpen dialog open',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  moveToTop: (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};
        toReturn.mustMatch = {};
        function order() {
          const actual = $('.ui-dialog')
            .map(function () {
              return +$(this).css('z-index');
            })
            .get();
          // eslint-disable-next-line prefer-rest-params
          const makeArgumentArray = $.makeArray(arguments);
          // eslint-disable-next-line prefer-rest-params
          toReturn.mustMatch[`${arguments[0]}`] = [actual, makeArgumentArray];
        }

        let focusOn = 'dialog1';
        const dialog1 = $('#dialog1').dialog({
          focus() {
            toReturn.dialogOneFocused = focusOn === 'dialog1';
          },
        });
        focusOn = 'dialog2';
        $('#dialog2').dialog({
          focus() {
            toReturn.dialogTwoFocused = focusOn === 'dialog2';
          },
        });
        order(100, 101);
        focusOn = 'dialog1';
        dialog1.dialog('moveToTop');
        order(102, 101);
        return toReturn;
      },
      [],
      (result) => {
        const { mustMatch } = result.value;
        delete result.value.mustMatch;
        const expectedTrue = {
          dialogOneFocused: 'dialog 1 focused',
          dialogTwoFocused: 'dialog 2 focused',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
        browser.assert.equal(Object.keys(mustMatch).length, 2);
        Object.keys(mustMatch).forEach((property) => {
          browser.assert.deepEqual(
            mustMatch[property][0],
            mustMatch[property][1],
            `${property} are equivalent`,
          );
        });
      },
    );
  },
  'moveToTop: content scroll stays intact': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const otherDialog = $('#dialog1').dialog();
        const scrollDialog = $('#form-dialog').dialog({
          height: 200,
        });
        scrollDialog.scrollTop(50);
        setTimeout(() => {
          toReturn.scrollNoChangeFirst = scrollDialog.scrollTop() === 50;
          otherDialog.dialog('moveToTop');
          setTimeout(() => {
            toReturn.scrollNoChangeSecond = scrollDialog.scrollTop() === 50;
            done(toReturn);
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          scrollNoChangeFirst: 'scroll top first',
          scrollNoChangeSecond: 'scroll top second',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  openMethod: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const expected = $('<div></div>').dialog();
        const actual = expected.dialog('open');
        setTimeout(() => {
          toReturn.openIsChainable = actual === expected;
          const element = $('<div></div>').dialog({ autoOpen: false });
          setTimeout(() => {
            toReturn.dialogHiddenBeforeOpenCalled =
              element.dialog('widget').is(':hidden') &&
              !element.dialog('widget').is(':visible');
            element.dialog('open');
            setTimeout(() => {
              toReturn.dialogVisibleAfterOpenCalled =
                element.dialog('widget').is(':visible') &&
                !element.dialog('widget').is(':hidden');
              done(toReturn);
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          openIsChainable: 'open is chainable',
          dialogHiddenBeforeOpenCalled:
            'dialog hidden before open method called',
          dialogVisibleAfterOpenCalled:
            'dialog visible after open method called',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  'Ensure form elements do not reset when opening a dialog': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const d1 = $(
          `<form><input type='radio' name='radio' id='a' value='a' checked='checked'>a</input>
            <input type='radio' name='radio' id='b' value='b'>b</input></form>`,
        )
          .appendTo('body')
          .dialog({ autoOpen: false });

        d1.find('#b').prop('checked', true);
        setTimeout(() => {
          toReturn.checkboxChecked1 = d1.find('input:checked').val() === 'b';
          d1.dialog('open');
          setTimeout(() => {
            toReturn.checkboxChecked2 = d1.find('input:checked').val() === 'b';
            done(toReturn);
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          checkboxChecked1: 'checkbox b is checked 1',
          checkboxChecked2: 'checkbox b is checked 2',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  '#8958: dialog can be opened while opening': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const element = $('<div>').dialog({
          autoOpen: false,
          modal: true,
          open() {
            done($('.ui-widget-overlay').length === 1);
          },
        });

        $('#favorite-animal')
          // We focus the input to start the test. Once it receives focus, the
          // dialog will open. Opening the dialog, will cause an element inside
          // the dialog to gain focus, thus blurring the input.
          .on('focus', function () {
            element.dialog('open');
          })

          // When the input blurs, the dialog is in the process of opening. We
          // try to open the dialog again, to make sure that dialogs properly
          // handle a call to the open() method during the process of the dialog
          // being opened.
          .on('blur', function () {
            element.dialog('open');
          })
          .trigger('focus');
      },
      [],
      (result) => {
        browser.assert.strictEqual(result.value, true, 'opened while opening');
      },
    );
  },
  '#5531: dialog width should be at least minWidth on creation': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const element = $('<div></div>').dialog({
          width: 200,
          minWidth: 300,
        });

        const element2 = $('<div></div>').dialog({
          minWidth: 300,
        });
        setTimeout(() => {
          toReturn.widthIsAtLeast300 =
            element2.dialog('option', 'width') >= 300;
        });

        setTimeout(() => {
          toReturn.widthIsMinWidth = element.dialog('option', 'width') === 300;
          element.dialog('option', 'width', 200);
          setTimeout(() => {
            toReturn.widthUnchangedWhenSetLessThanMin =
              element.dialog('option', 'width') === 300;
            element.dialog('option', 'width', 320);
            setTimeout(() => {
              toReturn.widthChangesWhenMoreThanMin =
                element.dialog('option', 'width') === 320;
              done(toReturn);
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          widthIsMinWidth: 'width is minWidth',
          widthUnchangedWhenSetLessThanMin:
            'width unchanged when set to < minWidth',
          widthChangesWhenMoreThanMin: 'width changed if set to > minWidth',
          widthIsAtLeast300: 'width is at least 300',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  appendTo: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const detached = $('<div>');
        const element = $('#dialog1').dialog({
          modal: true,
        });
        setTimeout(() => {
          toReturn.defaultsToBody = element
            .dialog('widget')
            .parent()[0]
            .isEqualNode(document.body);
          toReturn.overlayDefaultsToBody = $('.ui-widget-overlay')
            .parent()[0]
            .isEqualNode(document.body);
          element.dialog('destroy');

          element.dialog({
            appendTo: '.wrap',
            modal: true,
          });

          setTimeout(() => {
            toReturn.firstFoundElement = element
              .dialog('widget')
              .parent()[0]
              .isEqualNode($('#wrap1')[0]);

            toReturn.overlayFirstFoundElement = $('.ui-widget-overlay')
              .parent()[0]
              .isEqualNode($('#wrap1')[0]);
            toReturn.onlyAppendsOneElement =
              $('#wrap2 .ui-dialog').length === 0;
            toReturn.overlayOnlyAppendsOneElement =
              $('#wrap2 .ui-dialog').length === 0;
            element.dialog('destroy');

            element.dialog({
              appendTo: null,
              modal: true,
            });
            toReturn.appendNull = element
              .dialog('widget')
              .parent()[0]
              .isEqualNode(document.body);
            toReturn.overlayAppendNull = $('.ui-widget-overlay')
              .parent()[0]
              .isEqualNode(document.body);
            element.dialog('destroy');

            element
              .dialog({
                autoOpen: false,
                modal: true,
              })
              .dialog('option', 'appendTo', '#wrap1')
              .dialog('open');
            setTimeout(() => {
              toReturn.modifiedAfterInit = element
                .dialog('widget')
                .parent()[0]
                .isEqualNode($('#wrap1')[0]);
              toReturn.overlayModifiedAfterInit = $('.ui-widget-overlay')
                .parent()[0]
                .isEqualNode($('#wrap1')[0]);
              element.dialog('destroy');
              element.dialog({
                appendTo: detached,
                modal: true,
              });
              setTimeout(() => {
                toReturn.detachedJqueryObject = element
                  .dialog('widget')
                  .parent()[0]
                  .isEqualNode(detached[0]);
                toReturn.overlayDetachedJqueryObject = detached
                  .find('.ui-widget-overlay')
                  .parent()[0]
                  .isEqualNode(detached[0]);
                element.dialog('destroy');

                element.dialog({
                  appendTo: detached[0],
                  modal: true,
                });
                setTimeout(() => {
                  toReturn.detachedDOM = element
                    .dialog('widget')
                    .parent()[0]
                    .isEqualNode(detached[0]);
                  toReturn.overlayDetachedDOM = detached
                    .find('.ui-widget-overlay')
                    .parent()[0]
                    .isEqualNode(detached[0]);
                  element.dialog('destroy');

                  element
                    .dialog({
                      autoOpen: false,
                      modal: true,
                    })
                    .dialog('option', 'appendTo', detached);

                  setTimeout(() => {
                    toReturn.detachedViaOption = element
                      .dialog('widget')
                      .parent()[0]
                      .isEqualNode(detached[0]);
                    toReturn.overlayDetachedViaOption =
                      detached.find('.ui-widget-overlay').length === 0;

                    done(toReturn);
                  });
                });
              });
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          defaultsToBody: 'defaults to body',
          overlayDefaultsToBody: 'overlay defaults to body',
          firstFoundElement: 'first found element',
          overlayFirstFoundElement: 'overlay first found element',
          onlyAppendsOneElement: 'only appends to one element',
          overlayOnlyAppendsOneElement: 'overlay only appends to one element',
          appendNull: 'null',
          overlayAppendNull: 'overlay null',
          modifiedAfterInit: 'modified after init',
          overlayModifiedAfterInit: 'overlay modified after init',
          detachedJqueryObject: 'detached jQuery object',
          overlayDetachedJqueryObject: 'overlay detached jQuery object',
          detachedDOM: 'detached DOM',
          overlayDetachedDOM: 'overlay detached DOM',
          detachedViaOption: 'detached DOM element via option()',
          overlayDetachedViaOption: 'overlay detached DOM element via option()',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  autoOpen: (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};
        let element = $('<div></div>').dialog({ autoOpen: false });
        toReturn.autoOpenFalse = !element.dialog('widget').is(':visible');
        element.remove();

        element = $('<div></div>').dialog({ autoOpen: true });
        toReturn.autoOpenTrue = element.dialog('widget').is(':visible');

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          autoOpenFalse: '.dialog({ autoOpen: false })',
          autoOpenTrue: '.dialog({ autoOpen: true })',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  buttons: (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};
        toReturn.mustMatch = {};

        const $element = $('<div id="element"></div>');
        const buttons = {
          Ok(event) {
            toReturn.okButtonFiresCallback = true;
            toReturn.okButtonContext = $element[0].isEqualNode(this);
            toReturn.okButtonTarget = event.target.isEqualNode($btn[0]);
          },
          Cancel(event) {
            toReturn.cancelButtonFiresCallback = true;
            toReturn.cancelButtonContext = $element[0].isEqualNode(this);
            toReturn.cancelButtonTarget = event.target.isEqualNode($btn[1]);
          },
        };
        $element.dialog({ buttons });
        let $btn = $element
          .dialog('widget')
          .find('.ui-dialog-buttonpane button');
        toReturn.numberOfButtons = $btn.length === 2;

        let i = 0;

        toReturn.buttonText = Object.keys(buttons).reduce(function (
          result,
          key,
        ) {
          if (result !== false) {
            result = $btn[i].textContent === key;
          }
          // eslint-disable-next-line no-plusplus
          i++;
          return result;
        },
        null);

        toReturn.buttonsetClass = $btn.parent().hasClass('ui-dialog-buttonset');
        toReturn.dialogClass = $element.parent().hasClass('ui-dialog-buttons');

        $btn.trigger('click');

        const dialogButtons = $element.dialog('option', 'buttons');
        toReturn.mustMatch.dialogOptionButtonsGetter = [dialogButtons, buttons];

        const newButtons = {
          Close(ev) {
            toReturn.closeButtonFiresCallback = true;
            toReturn.closeButtonContext = $element[0].isEqualNode(this);
            toReturn.closeButtonTarget = $btn[0].isEqualNode(ev.target);
          },
        };

        $element.dialog('option', 'buttons', newButtons);
        $btn = $element.dialog('widget').find('.ui-dialog-buttonpane button');

        toReturn.mustMatch.dialogOptionButtonsSetter = [
          $element.dialog('option', 'buttons'),
          newButtons,
        ];
        toReturn.numberOfButtonsAfterSetter = $btn.length === 1;
        $btn.trigger('click');
        i = 0;
        $.each(newButtons, function (key) {
          toReturn[`textOfButton${i + 1}`] = $btn.eq(i).text() === key;
          i += 1;
        });

        $element.dialog('option', 'buttons', null);
        $btn = $element.dialog('widget').find('.ui-dialog-buttonpane button');
        toReturn.allButtonsRemoved = $btn.length === 0;
        toReturn.buttonSetRemoved =
          $element.find('.ui-dialog-buttonset').length === 0;
        toReturn.uiDialogButtonsClass = !$element
          .parent()
          .hasClass('ui-dialog-buttons');

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          numberOfButtons: 'number of buttons',
          buttonText: 'text of buttons',
          buttonsetClass: "buttonset has class 'ui-dialog-buttonset'",
          dialogClass: "dialog has class 'ui-dialog-buttons'",
          okButtonFiresCallback: 'ok button click fires callback',
          cancelButtonFiresCallback: 'cancel button click fires callback',
          okButtonContext: 'ok context of callback',
          okButtonTarget: 'ok button event target',
          cancelButtonContext: 'cancel context of callback',
          cancelButtonTarget: 'cancel button event target',
          closeButtonFiresCallback: 'close button click fires callback',
          closeButtonContext: 'close button context',
          closeButtonTarget: 'close event target',
          numberOfButtonsAfterSetter: 'number of buttons after setter',
          allButtonsRemoved: 'all buttons have been removed',
          buttonSetRemoved: 'buttonset has been removed',
          textOfButton1: 'Close button available after setter',
          uiDialogButtonsClass: 'no longer has ui-dialog-buttons class',
        };
        const { mustMatch } = result.value;
        delete result.value.mustMatch;

        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
        Object.keys(expectedTrue).forEach((property) => {
          browser.assert.equal(
            result.value[property],
            true,
            expectedTrue[property],
          );
        });
        browser.assert.equal(Object.keys(mustMatch).length, 2);
        Object.keys(mustMatch).forEach((property) => {
          browser.assert.deepEqual(
            mustMatch[property][0],
            mustMatch[property][1],
            `${property} are equivalent`,
          );
        });
      },
    );
  },
  'buttons - advanced': (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};
        const $element = $('<div></div>').dialog({
          buttons: [
            {
              text: 'a button',
              class: 'additional-class',
              id: 'my-button-id',
              // eslint-disable-next-line object-shorthand
              click: function () {
                toReturn.correctContext = this.isEqualNode($element[0]);
              },
              icon: 'ui-icon-cancel',
              showLabel: false,
            },
          ],
        });

        const $buttons = $element
          .dialog('widget')
          .find('.ui-dialog-buttonpane button');
        toReturn.numberOfButtons = $buttons.length === 1;
        toReturn.buttonId = $buttons.attr('id') === 'my-button-id';
        toReturn.buttonText =
          String.prototype.trim.call($buttons.text()) === 'a button';
        toReturn.buttonClasses = $buttons.hasClass('additional-class');
        toReturn.icon = $buttons.button('option', 'icon') === 'ui-icon-cancel';
        toReturn.showLabel = $buttons.button('option', 'showLabel') === false;

        $buttons.trigger('click');

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          numberOfButtons: 'number of buttons is correct',
          buttonId: 'button ID is correct',
          buttonText: 'button has correct text',
          buttonClasses: 'button has correct classes',
          icon: 'button has correct icon',
          showLabel: 'label configuration is correct',
          correctContext: 'context in click event is correct',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  '#9043: buttons with Array.prototype modification': (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};
        // eslint-disable-next-line no-extend-native
        Array.prototype.test = $.noop;
        const $element = $('<div></div>').dialog();
        toReturn.noButtonPane =
          $element.dialog('widget').find('.ui-dialog-buttonpane').length === 0;
        $element.remove();
        delete Array.prototype.test;

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          noButtonPane: 'button pane should not exist',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  closeOnEscape: (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};
        const $element = $('<div></div>').dialog({ closeOnEscape: false });

        toReturn.dialogOpenEscDisabled =
          $element.dialog('widget').is(':visible') &&
          !$element.dialog('widget').is(':hidden');
        $element
          .simulate('keydown', { keyCode: $.ui.keyCode.ESCAPE })
          .simulate('keypress', { keyCode: $.ui.keyCode.ESCAPE })
          .simulate('keyup', { keyCode: $.ui.keyCode.ESCAPE });
        toReturn.dialogOpenEscDisabledAfterEsc =
          $element.dialog('widget').is(':visible') &&
          !$element.dialog('widget').is(':hidden');
        $element.remove();

        const $newElement = $('<div></div>').dialog({ closeOnEscape: true });
        toReturn.dialogOpenEscEnabled =
          $newElement.dialog('widget').is(':visible') &&
          !$newElement.dialog('widget').is(':hidden');
        $newElement
          .simulate('keydown', { keyCode: $.ui.keyCode.ESCAPE })
          .simulate('keypress', { keyCode: $.ui.keyCode.ESCAPE })
          .simulate('keyup', { keyCode: $.ui.keyCode.ESCAPE });
        toReturn.dialogClosedEscEnabledAfterEsc =
          $newElement.dialog('widget').is(':hidden') &&
          !$newElement.dialog('widget').is(':visible');

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          dialogOpenEscDisabled:
            'dialog is open before pressing ESC, ESC disabled',
          dialogOpenEscDisabledAfterEsc:
            'dialog is open after pressing ESC, ESC disabled',
          dialogOpenEscEnabled:
            'dialog is open before pressing ESC, ESC enabled',
          dialogClosedEscEnabledAfterEsc:
            'dialog is closed after pressing ESC, ESC enabled',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  closeText: (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};

        let $element = $('<div></div>').dialog();
        toReturn.defaultCloseText =
          String.prototype.trim.call(
            $element.dialog('widget').find('.ui-dialog-titlebar-close').text(),
          ) === 'Close';
        $element.remove();

        $element = $('<div></div>').dialog({ closeText: 'foo' });
        toReturn.closeTextOnInit =
          String.prototype.trim.call(
            $element.dialog('widget').find('.ui-dialog-titlebar-close').text(),
          ) === 'foo';
        $element.remove();

        $element = $('<div></div>')
          .dialog()
          .dialog('option', 'closeText', 'bar');
        toReturn.closeTextViaOptionMethod =
          String.prototype.trim.call(
            $element.dialog('widget').find('.ui-dialog-titlebar-close').text(),
          ) === 'bar';
        $element.remove();

        $element = $('<div></div>')
          .dialog()
          .dialog('option', 'closeText', '<span>foo</span>');
        toReturn.closeTextIsEscaped =
          String.prototype.trim.call(
            $element.dialog('widget').find('.ui-dialog-titlebar-close').text(),
          ) === '<span>foo</span>';

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          defaultCloseText: 'default close text',
          closeTextOnInit: 'close text set on init',
          closeTextViaOptionMethod: 'close text set via option method',
          closeTextIsEscaped: 'close text is escaped',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  draggable: (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};

        const drag = function (element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        };
        let $element = $('<div></div>').dialog({ draggable: false });
        let $handle = $('.ui-resizable-se', $element.dialog('widget'));
        let offsetBefore = $element.dialog('widget').offset();
        drag($element, $handle, 50, -50);
        let offsetAfter = $element.dialog('widget').offset();
        toReturn.draggableFalseInit =
          offsetBefore.left === offsetAfter.left &&
          offsetBefore.top === offsetAfter.top;

        $element.dialog('option', 'draggable', true);
        $handle = $('.ui-resizable-se', $element.dialog('widget'));
        offsetBefore = $element.dialog('widget').offset();
        drag($element, $handle, 50, -50);
        offsetAfter = $element.dialog('widget').offset();
        toReturn.draggableTrueOption =
          50 - offsetAfter.left - offsetBefore.left <= 1 &&
          -50 - offsetAfter.top - offsetBefore.top <= 1;
        $element.remove();

        $element = $('<div></div>').dialog({ draggable: true });
        $handle = $('.ui-resizable-se', $element.dialog('widget'));
        offsetBefore = $element.dialog('widget').offset();
        drag($element, $handle, 50, -50);
        offsetAfter = $element.dialog('widget').offset();
        toReturn.draggableTrueInit =
          50 - offsetAfter.left - offsetBefore.left <= 1 &&
          -50 - offsetAfter.top - offsetBefore.top <= 1;

        $element.dialog('option', 'draggable', false);
        $handle = $('.ui-resizable-se', $element.dialog('widget'));
        offsetBefore = $element.dialog('widget').offset();
        drag($element, $handle, 50, -50);
        offsetAfter = $element.dialog('widget').offset();
        toReturn.draggableFalseOption =
          offsetBefore.left === offsetAfter.left &&
          offsetBefore.top === offsetAfter.top;

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          draggableFalseInit:
            'dialog cannot be dragged when draggable is set to false on init',
          draggableTrueOption:
            'dialog can be dragged when draggable is set to true via option',
          draggableTrueInit:
            'dialog can be dragged when draggable is set to true on init',
          draggableFalseOption:
            'dialog cannot be dragged when draggable is set to false via option',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  height: (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};

        let $element = $('<div></div>').dialog();
        toReturn.defaultHeight =
          Math.abs($element.dialog('widget').outerHeight() - 150) < 0.25;
        $element.remove();

        $element = $('<div></div>').dialog({ height: 237 });
        toReturn.explicitHeightInit =
          Math.abs($element.dialog('widget').outerHeight() - 237) < 0.25;
        $element.remove();

        $element = $('<div></div>').dialog();
        $element.dialog('option', 'height', 238);
        toReturn.explicitHeightOption =
          Math.abs($element.dialog('widget').outerHeight() - 238) < 0.25;
        $element.remove();

        $element = $('<div></div>')
          .css('padding', '20px')
          .dialog({ height: 240 });
        toReturn.explicitHeightWithPadding =
          Math.abs($element.dialog('widget').outerHeight() - 240) < 0.25;

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          defaultHeight: 'default height within 0.25 from expected',
          explicitHeightInit:
            'explicit height set on init within 0.25 from expected',
          explicitHeightOption:
            'explicit height set via option within 0.25 from expected',
          explicitHeightWithPadding:
            'explicit height with padding within 0.25 from expected',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  maxHeight: (browser) => {
    browser.execute(
      function () {
        const $ = jQuery;
        const toReturn = {};

        const drag = function (element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        };

        let $element = $('<div></div>').dialog({ maxHeight: 200 });
        let $handle = $('.ui-resizable-s', $element.dialog('widget'));
        drag($element, $handle, 1000, 1000);
        toReturn.maxHeightDragBottom =
          $element.dialog('widget').height() - 200 <= 1;
        $element.remove();

        $element = $('<div></div>').dialog({ maxHeight: 200 });
        $handle = $('.ui-resizable-n', $element.dialog('widget'));
        drag($element, $handle, -1000, -1000);
        toReturn.maxHeightDragTop =
          $element.dialog('widget').height() - 200 <= 1;
        $element.remove();

        $element = $('<div></div>')
          .dialog({ maxHeight: 200 })
          .dialog('option', 'maxHeight', 300);
        $handle = $('.ui-resizable-s', $element.dialog('widget'));
        drag($element, $handle, 1000, 1000);
        toReturn.maxHeightOption =
          $element.dialog('widget').height() - 300 <= 1;

        return toReturn;
      },
      [],
      (result) => {
        const expectedTrue = {
          maxHeightDragBottom:
            'height within 1 from maxHeight when dragged from bottom',
          maxHeightDragTop:
            'height within 1 from maxHeight when dragged from top',
          maxHeightOption: 'height within 1 when maxHeight set as option',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  maxWidth: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};

        const drag = function (element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        };
        let element = {};
        element = $('<div></div>').dialog({ maxWidth: 200 });
        setTimeout(() => {
          drag(element, '.ui-resizable-e', 1000, 1000);
          setTimeout(() => {
            toReturn.maxWidthE =
              element.dialog('widget').width() >= 199 &&
              element.dialog('widget').width() <= 201;
            // assert.close( element.dialog( "widget" ).width(), 200, 1, "maxWidth" );
            element.remove();

            element = $('<div></div>').dialog({ maxWidth: 200 });
            setTimeout(() => {
              drag(element, '.ui-resizable-w', -1000, -1000);
              setTimeout(() => {
                toReturn.maxWidthW =
                  element.dialog('widget').width() >= 199 &&
                  element.dialog('widget').width() <= 201;
                element.remove();
                element = $('<div></div>')
                  .dialog({ maxWidth: 200 })
                  .dialog('option', 'maxWidth', 300);
                setTimeout(() => {
                  drag(element, '.ui-resizable-w', -1000, -1000);
                  setTimeout(() => {
                    toReturn.maxWidthW2 =
                      element.dialog('widget').width() >= 299 &&
                      element.dialog('widget').width() <= 301;
                    done(toReturn);
                  });
                });
              });
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          maxWidthE: 'maxWidthE',
          maxWidthW: 'maxWidthW',
          maxWidthW2: 'maxWidthW2',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  minHeight: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        function drag(element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        }
        let element = {};
        element = $('<div></div>').dialog({ minHeight: 10 });
        drag(element, '.ui-resizable-s', -1000, -1000);
        setTimeout(() => {
          toReturn.minHeightS =
            element.dialog('widget').height() >= 9 &&
            element.dialog('widget').height() <= 11;
          element.remove();
          element = $('<div></div>').dialog({ minHeight: 10 });
          setTimeout(() => {
            drag(element, '.ui-resizable-n', 1000, 1000);
            setTimeout(() => {
              toReturn.minHeightN =
                element.dialog('widget').height() >= 9 &&
                element.dialog('widget').height() <= 11;
              element.remove();
              element = $('<div></div>')
                .dialog({ minHeight: 10 })
                .dialog('option', 'minHeight', 30);
              setTimeout(() => {
                drag(element, '.ui-resizable-n', 1000, 1000);
                setTimeout(() => {
                  toReturn.minHeightN2 =
                    element.dialog('widget').height() >= 29 &&
                    element.dialog('widget').height() <= 31;
                  done(toReturn);
                });
              });
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          minHeightS: 'minHeightS',
          minHeightN: 'minHeightN',
          minHeightN2: 'minHeightN2',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  minWidth: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        function drag(element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        }

        let element = {};
        element = $('<div></div>').dialog({ minWidth: 10 });
        drag(element, '.ui-resizable-e', -1000, -1000);
        setTimeout(() => {
          toReturn.minWidthE =
            element.dialog('widget').width() >= 9 &&
            element.dialog('widget').width() <= 11;
          element.remove();
          setTimeout(() => {
            element = $('<div></div>').dialog({ minWidth: 10 });
            drag(element, '.ui-resizable-w', 1000, 1000);
            setTimeout(() => {
              toReturn.minWidthW =
                element.dialog('widget').width() >= 9 &&
                element.dialog('widget').width() <= 11;
              element.remove();
              element = $('<div></div>')
                .dialog({ minWidth: 30 })
                .dialog('option', 'minWidth', 30);

              setTimeout(() => {
                drag(element, '.ui-resizable-w', 1000, 1000);
                setTimeout(() => {
                  toReturn.minWidthW2 =
                    element.dialog('widget').width() >= 29 &&
                    element.dialog('widget').width() <= 31;
                  done(toReturn);
                });
              });
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          minWidthE: 'minWidthE',
          minWidthW: 'minWidthW',
          minWidthW2: 'minWidthW2',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  'position, default center on window': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};

        const winWidth = $(window).width();
        const winHeight = $(window).height();
        const element = $('<div></div>').dialog();

        setTimeout(() => {
          const dialog = element.dialog('widget');
          const offset = dialog.offset();
          const leftOff =
            Math.round(winWidth / 2 - dialog.outerWidth() / 2) +
            $(window).scrollLeft();
          const topOff =
            Math.round(winHeight / 2 - dialog.outerHeight() / 2) +
            $(window).scrollTop();
          toReturn.leftPosition =
            offset.left >= leftOff - 1 && offset.left <= leftOff + 1;
          toReturn.topPosition =
            offset.top >= topOff - 1 && offset.top <= topOff + 1;
          done(toReturn);
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          leftPosition:
            'dialog left position of center on window on initialization',
          topPosition:
            'dialog top position of center on window on initialization',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  'position, right bottom at right bottom via ui.position args': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const winWidth = $(window).width();
        const winHeight = $(window).height();
        const element = $('<div></div>').dialog({
          position: {
            my: 'right bottom',
            at: 'right bottom',
          },
        });
        setTimeout(() => {
          const dialog = element.dialog('widget');
          const offset = dialog.offset();
          const leftOff =
            winWidth - dialog.outerWidth() + $(window).scrollLeft();
          const topOff =
            winHeight - dialog.outerHeight() + $(window).scrollTop();
          toReturn.leftPosition =
            offset.left >= leftOff - 1 && offset.left <= leftOff + 1;
          toReturn.topPosition =
            offset.top >= topOff - 1 && offset.top <= topOff + 1;
          done(toReturn);
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          leftPosition:
            'dialog left position of right bottom at right bottom on initialization',
          topPosition:
            'dialog top position of right bottom at right bottom on initialization',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  'position, at another element': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};

        const parent = $('<div></div>')
          .css({
            position: 'absolute',
            top: 400,
            left: 600,
            height: 10,
            width: 10,
          })
          .appendTo('body');

        const element = $('<div></div>').dialog({
          position: {
            my: 'left top',
            at: 'left top',
            of: parent,
            collision: 'none',
          },
        });

        const dialog = element.dialog('widget');
        let offset = 0;

        setTimeout(() => {
          offset = dialog.offset();
          toReturn.leftPositionOnInit =
            offset.left >= 599 && offset.left <= 601;
          toReturn.topPositionOnInit = offset.top >= 399 && offset.top <= 401;
          element.dialog('option', 'position', {
            my: 'left top',
            at: 'right bottom',
            of: parent,
            collision: 'none',
          });
          setTimeout(() => {
            offset = dialog.offset();
            toReturn.leftPositionViaSetting =
              offset.left >= 609 && offset.left <= 611;
            toReturn.topPositionViaSetting =
              offset.top >= 409 && offset.top <= 411;
            done(toReturn);
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          leftPositionOnInit:
            'dialog left position at another element on initialization',
          topPositionOnInit:
            'dialog top position at another element on initialization',
          leftPositionViaSetting:
            'dialog left position at another element via setting option',
          topPositionViaSetting:
            'dialog top position at another element via setting option',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  resizable: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};

        function drag(element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        }

        function shouldResize(element, dw, dh) {
          const d = element.dialog('widget');
          const handle = $('.ui-resizable-se', d);
          const heightBefore = element.height();
          const widthBefore = element.width();

          drag(element, handle, 50, 50);

          const heightAfter = element.height();
          const widthAfter = element.width();

          const actualDH = heightAfter - heightBefore;
          const actualDW = widthAfter - widthBefore;

          return Math.abs(actualDH - dh) <= 1 && Math.abs(actualDW - dw) <= 1;
        }

        let element = {};
        element = $('<div></div>').dialog();
        setTimeout(() => {
          toReturn.default = shouldResize(element, 50, 50);
          element.dialog('option', 'resizable', false);
          setTimeout(() => {
            toReturn.disabledAfterInit = shouldResize(element, 0, 0);
            element.remove();
            element = $('<div></div>').dialog({ resizable: false });
            setTimeout(() => {
              toReturn.disabledInInitOptions = shouldResize(element, 0, 0);
              element.dialog('option', 'resizable', true);
              setTimeout(() => {
                toReturn.enabledAfterInit = shouldResize(element, 50, 50);
                done(toReturn);
              });
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          default: '[default]',
          disabledAfterInit: 'disabled after init',
          disabledInInitOptions: 'disabled in init options',
          enabledAfterInit: 'enabled after init',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  title: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        let element = {};
        function titleText() {
          return element.dialog('widget').find('.ui-dialog-title').html();
        }

        element = $('<div></div>').dialog();

        // Some browsers return a non-breaking space and some return "&nbsp;"
        // so we generate a non-breaking space for comparison
        setTimeout(() => {
          toReturn.defaultTitle =
            $('<span>&#160;</span>').html() === titleText();
          toReturn.optionNotChanged =
            element.dialog('option', 'title') === null;
          element.remove();
          element = $("<div title='foo'>").dialog();
          setTimeout(() => {
            toReturn.titleInElementAttribute = titleText() === 'foo';
            toReturn.optionUpdatedFromAttribute =
              element.dialog('option', 'title') === 'foo';
            element.remove();
            element = $('<div></div>').dialog({ title: 'foo' });
            setTimeout(() => {
              toReturn.titleInInitOptions = titleText() === 'foo';
              toReturn.optionSetFromOptionsHash =
                element.dialog('option', 'title') === 'foo';
              element.remove();
              element = $("<div title='foo'>").dialog({ title: 'bar' });
              setTimeout(() => {
                toReturn.initOptionsOverrideElementAttribute =
                  titleText() === 'bar';
                toReturn.titleOptionSetFromOptionsHash =
                  element.dialog('option', 'title') === 'bar';
                element.remove();
                element = $('<div></div>')
                  .dialog()
                  .dialog('option', 'title', 'foo');

                setTimeout(() => {
                  toReturn.titleAfterInit = titleText() === 'foo';
                  element.remove();
                  element = $("<form><input name='title'></form>").dialog();
                  setTimeout(() => {
                    // Make sure attribute properties are properly ignored - #5742 - .attr() might return a DOMElement
                    toReturn.attributePropertiesDefault =
                      titleText() === $('<span>&#160;</span>').html();
                    toReturn.attributePropertiesOptionNotChanged =
                      element.dialog('option', 'title') === null;
                    done(toReturn);
                  });
                });
              });
            });
          });
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          defaultTitle: '[default]',
          optionNotChanged: 'option not changed',
          titleInElementAttribute: 'title in element attribute',
          optionUpdatedFromAttribute: 'option updated from attribute',
          titleInInitOptions: 'title in init options',
          optionSetFromOptionsHash: 'option set from options hash',
          initOptionsOverrideElementAttribute:
            'title in init options should override title in element attribute',
          titleOptionSetFromOptionsHash:
            'options set from options hash, title attr',
          titleAfterInit: 'title after init',
          attributePropertiesDefault: 'attribute properties [default]',
          attributePropertiesOptionNotChanged:
            'attribute properties option not changed',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  width: (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const element = $('<div></div>').dialog();
        setTimeout(() => {
          toReturn.defaultWidth =
            element.dialog('widget').width() >= 299 &&
            element.dialog('widget').width() <= 301;
        });

        const element2 = $('<div></div>').dialog({ width: 437 });
        setTimeout(() => {
          toReturn.explicitWidth =
            element2.dialog('widget').width() >= 437 &&
            element.dialog('widget').width() <= 437;
          done(toReturn);

          element2.dialog('option', 'width', 438);
          setTimeout(() => {
            toReturn.explicitWidthAfterInit =
              element2.dialog('widget').width() >= 438 &&
              element.dialog('widget').width() <= 438;
            done(toReturn);
          });
        }, 10);
      },
      [],
      (result) => {
        const expectedTrue = {
          defaultWidth: 'default width',
          explicitWidth: 'explicit width',
          explicitWidthAfterInit: 'explicit width after init',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  '#4826: setting resizable false toggles resizable on dialog': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        function drag(element, handle, dx, dy) {
          const d = element.dialog('widget');

          $(handle, d).simulate('mouseover').simulate('drag', {
            dx,
            dy,
          });
        }

        function shouldResize(element, dw, dh) {
          const d = element.dialog('widget');
          const handle = $('.ui-resizable-se', d);
          const heightBefore = element.height();
          const widthBefore = element.width();

          drag(element, handle, 50, 50);

          const heightAfter = element.height();
          const widthAfter = element.width();

          const actualDH = heightAfter - heightBefore;
          const actualDW = widthAfter - widthBefore;

          return Math.abs(actualDH - dh) <= 1 && Math.abs(actualDW - dw) <= 1;
        }
        let i = 0;
        const element = $('<div></div>').dialog({ resizable: false });
        setTimeout(() => {
          toReturn.default1 = shouldResize(element, 0, 0);
          for (i = 0; i < 2; i++) {
            element.dialog('close').dialog('open');
            setTimeout(
              (iteration) => {
                toReturn[`initResizableFalseToggle${iteration}`] = shouldResize(
                  element,
                  0,
                  0,
                );
              },
              0,
              [i + 1],
            );
          }
        });

        const element2 = $('<div></div>').dialog({ resizable: true });

        setTimeout(() => {
          toReturn.default2 = shouldResize(element2, 50, 50);
          for (i = 0; i < 2; i++) {
            element2
              .dialog('close')
              .dialog('option', 'resizable', false)
              .dialog('open');
            setTimeout(
              (iteration) => {
                toReturn[`optionResizableFalseToggle${iteration}`] =
                  shouldResize(element2, 0, 0);
                if (`${iteration}` === '2') {
                  done(toReturn);
                }
              },
              0,
              [i + 1],
            );
          }
        }, 50);
      },
      [],
      (result) => {
        const expectedTrue = {
          initResizableFalseToggle1:
            'initialized with resizable false toggle 1',
          initResizableFalseToggle2:
            'initialized with resizable false toggle 2',
          optionResizableFalseToggle1: 'option with resizable false toggle 1',
          optionResizableFalseToggle2: 'option with resizable false toggle 2',
          default1: 'default 0 0',
          default2: 'default 50 50',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  '#4421 - Focus lost from dialog which uses show-effect': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};
        const element = $('<div></div>').dialog({
          show: 'blind',
          focus() {
            setTimeout(() => {
              toReturn.dialogMaintainsFocus =
                element.dialog('widget').find(document.activeElement).length ===
                1;
              done(toReturn);
            });
          },
        });
      },
      [],
      (result) => {
        const expectedTrue = {
          dialogMaintainsFocus: 'dialog maintains focus',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
  'Open followed by close during show effect': (browser) => {
    browser.executeAsync(
      function (done) {
        const $ = jQuery;
        const toReturn = {};

        const element = $('<div></div>').dialog({
          show: 'blind',
          close() {
            toReturn.closedProperlyDuringAnimation = true;
            done(toReturn);
          },
        });

        setTimeout(() => {
          element.dialog('close');
        }, 100);
      },
      [],
      (result) => {
        const expectedTrue = {
          closedProperlyDuringAnimation:
            'dialog closed properly during animation',
        };
        browser.assert.equal(
          Object.keys(expectedTrue).length,
          Object.keys(result.value).length,
        );
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
