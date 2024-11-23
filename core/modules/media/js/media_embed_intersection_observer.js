/**
 * @file
 * Attaches the mediaEmbedIntersectionObserver behavior.
 */

((Drupal) => {
  Drupal.behaviors.mediaEmbedIntersectionObserver = {
    attach(context) {
      // Select all elements with the class '.media-oembed-content' and the 'data-src' attribute.
      const elements = context.querySelectorAll(
        '.media-oembed-content[data-src]',
      );

      // Check if matching elements exist.
      if (elements.length > 0) {
        // Iterate over each matching element.
        elements.forEach((frame) => {
          // Create an IntersectionObserver to handle lazy loading.
          const observer = new IntersectionObserver((entries, observer) => {
            // Iterate over each intersection entry.
            entries.forEach((entry) => {
              // If the element is in view, set the 'src' attribute and stop observing.
              if (entry.isIntersecting) {
                frame.setAttribute('src', frame.dataset.src);
                observer.unobserve(entry.target);
              }
            });
          });

          // Start observing the current element.
          observer.observe(frame);
        });
      }
    },
  };
})(Drupal);
