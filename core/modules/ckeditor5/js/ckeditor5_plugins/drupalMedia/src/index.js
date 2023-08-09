/* eslint-disable import/no-extraneous-dependencies */
// cspell:ignore mediaimagetextalternative mediaimagetitle drupalmediacaption

import DrupalMedia from './drupalmedia';

// cspell:ignore drupallinkmedia
import DrupalLinkMedia from './drupallinkmedia/drupallinkmedia';

// cspell:ignore drupalelementstyle
import DrupalElementStyle from './drupalelementstyle';

import DrupalMediaCaption from './drupalmediacaption';

// cspell:ignore mediaimagetextalternative
import MediaImageTextAlternative from './mediaimagetextalternative';
import MediaImageTextAlternativeEditing from './mediaimagetextalternative/mediaimagetextalternativeediting';
import MediaImageTextAlternativeUi from './mediaimagetextalternative/mediaimagetextalternativeui';

// cspell:ignore mediaimagetitle
import MediaImageTitle from './mediaimagetitle';
import MediaImageTitleEditing from './mediaimagetitle/mediaimagetitleediting';
import MediaImageTitleUi from './mediaimagetitle/mediaimagetitleui';

/**
 * @private
 */
export default {
  DrupalMedia,
  MediaImageTextAlternative,
  MediaImageTextAlternativeEditing,
  MediaImageTextAlternativeUi,
  MediaImageTitle,
  MediaImageTitleEditing,
  MediaImageTitleUi,
  DrupalLinkMedia,
  DrupalMediaCaption,
  DrupalElementStyle,
};
