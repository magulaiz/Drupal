/**
 * @file
 * Back to top file.
 */

((Drupal) => {
  const backToTop = document.querySelector('.scroll-top__button');

  // Function to toggle visibility of the button based on scroll position
  const toggleButtonVisibility = () => {
    if (window.scrollY > 200) {
      // Adjust the value as needed
      backToTop.style.display = 'block';
    } else {
      backToTop.style.display = 'none';
    }
  };

  // Add the scroll event listener to toggle button visibility
  window.addEventListener('scroll', toggleButtonVisibility);

  // Initial check in case the user has already scrolled down
  toggleButtonVisibility();

  backToTop.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})(Drupal);
