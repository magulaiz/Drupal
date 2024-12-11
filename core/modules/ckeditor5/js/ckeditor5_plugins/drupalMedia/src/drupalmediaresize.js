import { Plugin } from 'ckeditor5/src/core';
import MediaResizeButtons from './drupalmediaresize/drupalmediaresizebuttons';
import MediaResizeEditing from './drupalmediaresize/drupalmediaresizeediting';
import MediaResizeHandles from './drupalmediaresize/drupalmediaresizehandles';

/**
 * The media resize plugin.
 *
 * It adds a possibility to resize inserted media using drag and drop handles.
 */
export default class DrupalMediaResize extends Plugin {

  /**
   * @inheritDoc
   */
  static get requires() {
    return [ MediaResizeEditing, MediaResizeHandles, MediaResizeButtons ];
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'DrupalMediaResize';
  }
}
