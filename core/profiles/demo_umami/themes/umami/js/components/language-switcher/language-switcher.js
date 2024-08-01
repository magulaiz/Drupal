/**
 * @file
 * Language switcher block script.
 */

(function umamiLanguageSwitcherBlocksScript(Drupal) {
  Drupal.behaviors.umamiLanguageSwitcherBlocks = {
    attach: function (context) {

      let windowWidth = window.innerWidth;

      function handleLanguageSwitcher() {
        const languageSwitcherBlocks = context.querySelectorAll('.umami-language-switcher');
        languageSwitcherBlocks.forEach((block) => {
          const toggleButton = block.querySelector('.umami-language-switcher__toggle');
          const languageList = block.querySelector('.umami-language-switcher__content');
          toggleButton.removeAttribute('hidden');
          languageList.setAttribute('hidden', 'hidden');
          toggleButton.addEventListener('click', () => {
            const hidden = languageList.hasAttribute('hidden');
            languageList.toggleAttribute('hidden', !hidden);
            toggleButton.setAttribute('aria-expanded', hidden);
          });
        });
      }

      function handleReset() {
        const languageSwitcherBlocks = context.querySelectorAll('.umami-language-switcher');
        languageSwitcherBlocks.forEach((block) => {
          const toggleButton = block.querySelector('.umami-language-switcher__toggle');
          const languageList = block.querySelector('.umami-language-switcher__content');
          toggleButton.setAttribute('hidden', 'hidden');
          languageList.removeAttribute('hidden');
          toggleButton.setAttribute('aria-expanded', 'false');
        });
      }

      // Initial check.
      if (windowWidth <= 768) {
        setTimeout(handleLanguageSwitcher, 250);
      } else {
        setTimeout(handleReset, 250);
      }

      // Check after window resize.
      function handleCheckIfWindowActuallyResized() {
        if (window.innerWidth === windowWidth) {
          return
        } else {
          windowWidth = window.innerWidth;
          if (windowWidth <= 768) {
            handleLanguageSwitcher();
          } else {
            handleReset();
          }
        }
      }

      window.addEventListener('resize', Drupal.debounce(handleCheckIfWindowActuallyResized, 50, false));

    }
  };
}(Drupal));
