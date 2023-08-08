<?php

namespace Drupal\Core\Template;

use Drupal\Component\HtmlAttribute\HtmlAttributeCollection;
use Drupal\Core\Site\Settings;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityPolicyInterface;

/**
 * Default sandbox policy for Twig templates.
 *
 * Twig's sandbox extension is usually used to evaluate untrusted code by
 * limiting access to potentially unsafe properties or methods. Since we do not
 * use ViewModels when passing objects to Twig templates, we limit what those
 * objects can do by only loading certain classes, method names, and method
 * names with an allowed prefix. All object properties may be accessed.
 */
class TwigSandboxPolicy implements SecurityPolicyInterface {

  /**
   * An array of allowed methods in the form of methodName => TRUE.
   *
   * @var array
   */
  protected $allowed_methods;

  /**
   * Allowed method prefixes.
   *
   * Any method starting with one of these prefixes will be allowed.
   *
   * @var array
   */
  protected $allowed_prefixes;

  /**
   * An array of class names for which any method calls are allowed.
   *
   * @var array
   */
  protected $allowed_classes;

  /**
   * Constructs a new TwigSandboxPolicy object.
   */
  public function __construct() {
    $this->allowed_classes = $this->getAllowedClasses();
    $this->allowed_methods = $this->getAllowedMethods();
    $this->allowed_prefixes = $this->getAllowedPrefixes();
  }

  /**
   * Returns the list of allowed classes from the settings.
   *
   * @return string[]
   *   The list of allowed classes from the settings.
   */
  protected function getAllowedClasses(): array {
    if ($this->allowed_classes === NULL) {
      // Allow settings.php to override our default allowed classes, methods,
      // and prefixes.
      $allowed_classes = $this->getSettings('twig_sandbox_allowed_classes', [
        // Allow any operations on the HtmlAttributeCollection object as it is
        // intended to be changed from a Twig template, for example calling
        // addClass().
        HtmlAttributeCollection::class,
      ]);
      // BC layer to support earlier Attribute class.
      if (in_array('Drupal\Core\Template\Attribute', $allowed_classes) && !in_array(HtmlAttributeCollection::class, $allowed_classes)) {
        @trigger_error('\Drupal\Core\Template\Attribute as an allowed class in $settings[\'twig_sandbox_allowed_classes\'] is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\Component\HtmlAttribute\HtmlAttributeCollection instead. See https://www.drupal.org/node/3070485', E_USER_DEPRECATED);
        $allowed_classes[] = HtmlAttributeCollection::class;
      }
      // Flip the array so we can check using isset().
      $this->allowed_classes = array_flip($allowed_classes);
    }
    return $this->allowed_classes;
  }

  /**
   * Returns the list of allowed methods from the settings.
   *
   * @return string[]
   *   The list of allowed methods from the settings.
   */
  protected function getAllowedMethods(): array {
    if ($this->allowed_methods === NULL) {
      $allowed_methods = $this->getSettings('twig_sandbox_allowed_methods', [
        // Only allow idempotent methods.
        'id',
        'label',
        'bundle',
        'get',
        '__toString',
        'toString',
      ]);
      // Flip the array so we can check using isset().
      $this->allowed_methods = array_flip($allowed_methods);
    }
    return $this->allowed_methods;
  }

  /**
   * Returns the list of allowed prefixes from the settings.
   *
   * @return string[]
   *   The list of allowed prefixes from the settings.
   */
  protected function getAllowedPrefixes(): array {
    if ($this->allowed_prefixes === NULL) {
      $this->allowed_prefixes = $this->getSettings('twig_sandbox_allowed_prefixes', [
        'get',
        'has',
        'is',
      ]);
    }
    return $this->allowed_prefixes;
  }

  /**
   * Returns a setting via Settings::get.
   *
   * @param string $name
   *   The name of the setting to return.
   * @param mixed $default
   *   (optional) The default value to use if this setting is not set.
   *
   * @return mixed
   *   The value of the setting, the provided default if not set.
   */
  protected function getSettings(string $name, $default = NULL) {
    return Settings::get($name, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function checkSecurity($tags, $filters, $functions): void {}

  /**
   * {@inheritdoc}
   */
  public function checkPropertyAllowed($obj, $property): void {}

  /**
   * {@inheritdoc}
   */
  public function checkMethodAllowed($obj, $method): void {
    foreach ($this->getAllowedClasses() as $class => $key) {
      if ($obj instanceof $class) {
        return;
      }
    }

    // Return quickly for an exact match of the method name.
    if (isset($this->getAllowedMethods()[$method])) {
      return;
    }

    // If the method name starts with an allowed prefix, allow it. Note:
    // strpos() is between 3x and 7x faster than preg_match() in this case.
    foreach ($this->getAllowedPrefixes() as $prefix) {
      if (str_starts_with($method, $prefix)) {
        return;
      }
    }

    throw new SecurityError(sprintf('Calling "%s" method on a "%s" object is not allowed.', $method, get_class($obj)));
  }

}
