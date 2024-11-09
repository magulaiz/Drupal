<?php

namespace Drupal\Core\Theme;

/**
 * Provides a value object for a theme hook.
 *
 * @internal
 *
 * @todo This class exists as an iterative improvement to the Theme API but is
 *   marked internal and final until it is complete. For next steps, see
 *   https://www.drupal.org/node/2869859. For finalizing the removal of
 *   \ArrayAccess, see https://www.drupal.org/i/2873117.
 */
final class ThemeHook implements \ArrayAccess {

  /**
   * The type of provider of the theme hook.
   *
   * This is automatically derived and does not need to be specified.
   *
   * @var string
   */
  protected string $type = '';

  /**
   * The provider of the theme hook.
   *
   * This is automatically derived and does not need to be specified.
   *
   * @var string
   */
  protected string $provider = '';

  /**
   * The directory path of the theme or module.
   *
   * This is automatically derived and does not need to be specified.
   *
   * @var string
   */
  protected string $themePath = '';

  /**
   * An array of default values to be passed to the template.
   *
   * @var mixed[]|null
   */
  protected ?array $variables = NULL;

  /**
   * The name of the renderable element to pass to the theme function.
   *
   * @var string
   */
  protected string $renderElement = '';

  /**
   * A regular expression pattern.
   *
   * @var string|null
   */
  protected ?string $pattern = NULL;

  /**
   * The base theme hook name, if one exists.
   *
   * @var string|null
   */
  protected ?string $base = NULL;

  /**
   * An array of files to be included.
   *
   * The file paths must be relative to the Drupal root directory.
   *
   * @var string[]
   */
  protected array $includes = [];

  /**
   * The file the implementation resides in.
   *
   * This file will be included prior to the theme being rendered, to make sure
   * that the function or preprocess function (as needed) is actually loaded.
   *
   * @var string
   */
  protected string $file = '';

  /**
   * The template name for this theme implementation.
   *
   * @var string|null
   */
  protected ?string $template = NULL;

  /**
   * The path to the theme implementation, if it exists.
   *
   * The path must be relative to the Drupal root directory.
   *
   * @var string|null
   */
  protected ?string $path = NULL;

  /**
   * A list of functions used to preprocess this data.
   *
   * @var string[]
   */
  protected array $preprocessFunctions = [];

  /**
   * Whether the list of preprocess functions is incomplete.
   *
   * @var bool
   */
  protected bool $incompletePreprocessFunctions = FALSE;

  /**
   * Whether standard preprocess functions should be ignored.
   *
   * @var bool
   */
  protected bool $overridePreprocessFunctions = FALSE;

  /**
   * Stores extra data.
   *
   * @var mixed[]
   *
   * @todo Remove in https://www.drupal.org/i/2873117
   */
  protected array $extra = [];

  /**
   * Constructs a new ThemeHook.
   *
   * @param string $name
   *   The machine name of the theme hook.
   */
  protected function __construct(protected string $name) {
  }

  /**
   * Creates a new ThemeHook instance.
   *
   * @param string $name
   *   The machine name of the theme hook.
   *
   * @return static
   */
  public static function create(string $name) {
    return new static($name);
  }

  /**
   * Creates a new ThemeHook instance based on an existing ThemeHook.
   *
   * This does not copy every single property, only those considered default.
   *
   * @param string $name
   *   The machine name of the theme hook.
   * @param \Drupal\Core\Theme\ThemeHook $other
   *   Another theme hook to use as the basis for a new theme hook.
   *
   * @return static
   */
  public static function createFromExisting($name, ThemeHook $other) {
    $instance = static::create($name);
    $instance->handleDefaultValues($other);
    return $instance;
  }

  /**
   * Constructs a new ThemeHook using the legacy array-based format.
   *
   * @param string $name
   *   The machine name of the theme hook.
   * @param array $values
   *   An array of values.
   *
   * @return static
   */
  public static function createFromLegacy(string $name, array $values) {
    $instance = static::create($name);
    foreach ($values as $key => $value) {
      $instance->offsetSet($key, $value);
    }
    return $instance;
  }

  /**
   * Merges another theme hook into the values of the current theme hook.
   *
   * This does not modify the current theme hook, but returns a new instance.
   *
   * @param \Drupal\Core\Theme\ThemeHook $other
   *   Another theme hook to use as the basis for a new theme hook.
   *
   * @return static
   */
  public function merge(ThemeHook $other): static {
    $result = clone $this;

    // If this object doesn't have a value, use the value from the other object.
    if (!$result->getProviderType()) {
      $result->setProviderType($other->getProviderType());
    }
    if (!$result->getFile()) {
      $result->setFile($other->getFile());
    }
    if (!$result->getThemePath()) {
      $result->setThemePath($other->getThemePath());
    }
    if (!$result->getPath()) {
      $result->setPath($other->getPath());
    }
    if (!$result->isPreprocessOverridden()) {
      $result->overridePreprocess($other->isPreprocessOverridden());
    }
    if ($other->hasVariables() && !$result->hasVariables()) {
      $result->setVariables($other->getVariables());
    }
    if (!$result->getPattern()) {
      $result->setPattern($other->getPattern());
    }
    if (!$result->getBaseHook()) {
      $result->setBaseHook($other->getBaseHook());
    }
    if ($other->getRenderElement() && !$result->getRenderElement()) {
      $result->setRenderElement($other->getRenderElement());
    }
    if (!$result->getTemplate()) {
      $result->setTemplate($other->getTemplate());
    }

    // If the other object does not yet have a complete list of preprocess
    // functions, mark the result as incomplete.
    if ($other->isIncomplete()) {
      $result->markIncomplete();
    }

    // Merge all includes together.
    $result->setIncludes(array_merge($other->getIncludes(), $result->getIncludes()));

    // Check for the override flag and prevent the cached variable preprocessors
    // from being used. This allows themes or theme engines to remove variable
    // preprocessors set earlier in the registry build.
    if (!$result->isPreprocessOverridden()) {
      $result->setPreprocessFunctions(array_merge($other->getPreprocessFunctions(), $result->getPreprocessFunctions()));
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function &offsetGet(mixed $name): mixed {
    $value = NULL;
    $name = str_replace(' ', '_', $name);
    if (property_exists($this, $name)) {
      $value = &$this->{$name};
    }
    else {
      if (isset($this->extra[$name])) {
        $value = &$this->extra[$name];
      }
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet(mixed $name, mixed $value): void {
    $name = str_replace(' ', '_', $name);
    if (property_exists($this, $name)) {
      $this->{$name} = $value;
    }
    else {
      $this->extra[$name] = $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset(mixed $name): void {
    $name = str_replace(' ', '_', $name);
    if (property_exists($this, $name)) {
      $reflection = new \ReflectionClass($this);
      $this->{$name} = $reflection->getDefaultProperties()[$name];
    }
    else {
      unset($this->extra[$name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function offsetExists(mixed $name): bool {
    $name = str_replace(' ', '_', $name);
    if (property_exists($this, $name)) {
      return isset($this->{$name});
    }
    else {
      return isset($this->extra[$name]);
    }
  }

  /**
   * Gets the directory path of the theme or module.
   *
   * @return string
   *   The directory path of the theme or module.
   */
  public function getThemePath(): string {
    return $this->themePath;
  }

  /**
   * Sets the directory path of the theme or module.
   *
   * @param string $theme_path
   *   The directory path of the theme or module.
   *
   * @return $this
   */
  public function setThemePath(string $theme_path): static {
    $this->themePath = $theme_path;

    return $this;
  }

  /**
   * Gets the type of provider of the theme hook.
   *
   * @return string
   *   The type of provider of the theme hook. May be one of:
   *   - 'module': A module is being checked for theme implementations.
   *   - 'base_theme_engine': A theme engine is being checked for a theme that
   *     is a parent of the actual theme being used.
   *   - 'theme_engine': A theme engine is being checked for the actual theme
   *     being used.
   *   - 'base_theme': A base theme is being checked for theme implementations.
   *   - 'theme': The actual theme in use is being checked.
   */
  public function getProviderType(): string {
    return $this->type;
  }

  /**
   * Sets the type of provider of the theme hook.
   *
   * @param string $type
   *   The type of provider of the theme hook.
   *
   * @return $this
   */
  public function setProviderType($type): static {
    $this->type = $type;

    return $this;
  }

  /**
   * Gets the provider of the theme hook.
   *
   * @return string
   *   The provider of the theme hook.
   */
  public function getProvider(): string {
    return $this->type;
  }

  /**
   * Sets the provider of the theme hook.
   *
   * @param string $type
   *   The provider of the theme hook.
   *
   * @return $this
   */
  public function setProvider($type): static {
    $this->type = $type;

    return $this;
  }

  /**
   * Returns the default values to be passed to the template.
   *
   * @return mixed[]|null
   *   An associative array where the keys are names of variables, and the
   *   values are the default values if they are not given in the render array.
   *   If this returns NULL, self::getRenderElement() will be used instead.
   */
  public function getVariables(): ?array {
    return $this->variables;
  }

  /**
   * Sets the default values to be passed to the template.
   *
   * Only used for #theme in render array. Template implementations receive each
   * array key as a variable in the template file (so they must be legal
   * PHP/Twig variable names). Function implementations are passed the variables
   * in a single $variables function argument. If you are using these variables
   * in a render array, prefix the variable names defined here with a #.
   *
   * @param mixed[]|null $variables
   *   An associative array where the keys are names of variables, and the
   *   values are the default values if they are not given in the render array.
   *
   * @return $this
   */
  public function setVariables($variables): static {
    $this->variables = $variables;

    return $this;
  }

  /**
   * Returns whether this theme hook has variables to pass to the template.
   *
   * @return bool
   *   TRUE if the theme hook uses variables, FALSE otherwise.
   */
  public function hasVariables(): bool {
    return isset($this->variables);
  }

  /**
   * Gets the name of the renderable element.
   *
   * @return string
   *   The name of the renderable element to pass to the theme function.
   */
  public function getRenderElement(): string {
    return $this->renderElement;
  }

  /**
   * Sets the name of the renderable element.
   *
   * Used for render element items only. This name is used as the name of the
   * variable that holds the renderable element or tree in preprocess functions.
   *
   * @param string $render_element
   *   The name of the renderable element to pass to the theme function.
   *
   * @return $this
   */
  public function setRenderElement(string $render_element): static {
    $this->renderElement = $render_element;

    return $this;
  }

  /**
   * Gets the regular expression pattern.
   *
   * @return string|null
   *   A regular expression pattern, if one exists.
   */
  public function getPattern(): ?string {
    return $this->pattern;
  }

  /**
   * Sets the regular expression pattern.
   *
   * A pattern allows the theme implementation to have a dynamic name.
   *
   * The convention is to use __ to differentiate the dynamic portion of the
   * theme. For example, to allow forums to be themed individually, the pattern
   * might be: 'forum__'. Then, when the forum is rendered, following render
   * array can be used:
   *   @code
   *   $render_array = [
   *     '#theme' => ['forum__' . $tid, 'forum'],
   *     '#forum' => $forum,
   *   ];
   *   @endcode
   *
   * @param string|null $pattern
   *   A regular expression pattern, or NULL to set no pattern.
   *
   * @return $this
   */
  public function setPattern($pattern): static {
    $this->pattern = $pattern;

    return $this;
  }

  /**
   * Returns if this theme hook has a regular expression pattern set.
   *
   * @return bool
   *   TRUE if a regular expression pattern is set, FALSE otherwise.
   */
  public function hasPattern(): bool {
    return !is_null($this->pattern);
  }

  /**
   * Gets the base theme hook name.
   *
   * @return string|null
   *   The name of a theme hook to use as the basis for this theme hook, or NULL
   *   if no base hook exists.
   */
  public function getBaseHook(): ?string {
    return $this->base;
  }

  /**
   * Sets the base theme hook name.
   *
   * Used for theme suggestions only.
   *
   * Instead of this suggestion's implementation being used directly, the base
   * hook will be invoked with this implementation as its first suggestion. The
   * base hook's files will be included and the base hook's preprocess functions
   * will be called in addition to any suggestion's preprocess functions. If an
   * implementation of hook_theme_suggestions_HOOK() (where HOOK is the base
   * hook) changes the suggestion order, a different suggestion may be used in
   * place of this suggestion. If after hook_theme_suggestions_HOOK() this
   * suggestion remains the first suggestion, then this suggestion's function or
   * template will be used to generate the rendered output.
   *
   * @param string|null $base_hook
   *   The name of a theme hook to use as the basis for this theme hook, or NULL
   *   if no base hook exists.
   *
   * @return $this
   */
  public function setBaseHook(?string $base_hook): static {
    $this->base = $base_hook;

    return $this;
  }

  /**
   * Gets the array of files to be included.
   *
   * @return string[]
   *   An array of files to be included. The file paths are relative to the
   *   Drupal root directory.
   */
  public function getIncludes(): array {
    return $this->includes;
  }

  /**
   * Sets the array of files to be included.
   *
   * @param string[] $includes
   *   An array of files to be included. The file paths must be relative to the
   *   Drupal root directory.
   *
   * @return $this
   */
  public function setIncludes(array $includes): static {
    $this->includes = $includes;

    return $this;
  }

  /**
   * Adds a file to be included.
   *
   * @param string $include
   *   A path relative to the Drupal root directory, or NULL if no path is set.
   *
   * @return $this
   */
  public function addInclude($include): static {
    $includes = $this->getIncludes();
    $includes[] = $include;
    $this->setIncludes($includes);

    return $this;
  }

  /**
   * Returns whether any files are specified for inclusion.
   *
   * @return bool
   *   TRUE if files exist to be included, FALSE otherwise.
   */
  public function hasIncludes(): bool {
    return !empty($this->includes);
  }

  /**
   * Gets the file the theme implementation resides in.
   *
   * @return string
   *   The file the theme implementation resides in.
   */
  public function getFile(): string {
    return $this->file;
  }

  /**
   * Gets the file the theme implementation resides in.
   *
   * This file will be included prior to the theme being rendered, to make sure
   * that the function or preprocess function (as needed) is actually loaded.
   *
   * @param string $file
   *   The file the theme implementation resides in.
   *
   * @return $this
   */
  public function setFile($file): static {
    $this->file = $file;

    return $this;
  }

  /**
   * Gets the template name to use for the theme implementation.
   *
   * @return string
   *   The template name for this theme implementation.
   */
  public function getTemplate(): ?string {
    return $this->template;
  }

  /**
   * Sets the template name to use for the theme implementation.
   *
   * For example, if a module registers the 'search_result' theme hook,
   * 'search-result' will be assigned as its template name.
   *
   * @param string|null $template
   *   The template name for this theme implementation. Do not add 'html.twig'
   *   on the end of the template name. The extension will be added
   *   automatically by the default rendering engine (which is Twig).
   *
   * @return $this
   */
  public function setTemplate($template): static {
    $this->template = $template;

    return $this;
  }

  /**
   * Gets the path to the theme implementation, if it exists.
   *
   * @return string|null
   *   A path relative to the Drupal root directory, or NULL if no path is set.
   */
  public function getPath(): ?string {
    return $this->path;
  }

  /**
   * Overrides the path to the file containing the theme implementation.
   *
   * If this is used, self::setTemplate() should also be called.
   *
   * @param string|null $path
   *   A path relative to the Drupal root directory, or NULL to use the default
   *   theme path.
   *
   * @return $this
   */
  public function setPath($path): static {
    $this->path = $path;

    return $this;
  }

  /**
   * Gets the list of functions used to preprocess this data.
   *
   * @return string[]
   *   An array of functions to be called during the preprocess phase.
   */
  public function getPreprocessFunctions(): array {
    return $this->preprocessFunctions;
  }

  /**
   * Sets the list of functions used to preprocess this data.
   *
   * Ordinarily this won't be used; it's automatically filled in. By default,
   * for a module this will be filled in as template_preprocess_HOOK. For a
   * theme this will be filled in as twig_preprocess and twig_preprocess_HOOK as
   * well as themename_preprocess and themename_preprocess_HOOK.
   *
   * @param string[] $preprocess_functions
   *   An array of functions to be called during the preprocess phase.
   *
   * @return $this
   */
  public function setPreprocessFunctions(array $preprocess_functions): static {
    $this->preprocessFunctions = array_values(array_unique($preprocess_functions));

    return $this;
  }

  /**
   * Indicates if a given preprocess function will be used for this theme hook.
   *
   * @param string $preprocess_function
   *   The name of a preprocess function.
   *
   * @return bool
   *   TRUE if this preprocess function will be used for this theme hook,
   *   FALSE otherwise.
   */
  public function hasPreprocessFunction($preprocess_function): bool {
    return in_array($preprocess_function, $this->getPreprocessFunctions());
  }

  /**
   * Adds a preprocess function to this theme hook.
   *
   * @param string $preprocess_function
   *   The name of a preprocess function.
   *
   * @return $this
   */
  public function addPreprocessFunction($preprocess_function): static {
    $preprocess_functions = $this->getPreprocessFunctions();
    $preprocess_functions[] = $preprocess_function;
    $this->setPreprocessFunctions($preprocess_functions);

    return $this;
  }

  /**
   * Determines if standard preprocess functions should be ignored.
   *
   * @return bool
   *   TRUE if standard preprocess functions should be ignored, FALSE otherwise.
   */
  public function isPreprocessOverridden(): bool {
    return $this->overridePreprocessFunctions;
  }

  /**
   * Prevents standard preprocess functions from running if set to TRUE.
   *
   * This can be used to give a theme FULL control over how variables are set.
   * For example, if a theme wants total control over how certain variables in
   * the page.html.twig are set, this can be set to true. Keep in mind that when
   * this is used by a theme, that theme becomes responsible for making sure
   * necessary variables are set.
   *
   * @param bool $status
   *   (optional) TRUE if standard preprocess functions should be ignored, FALSE
   *   otherwise. Defaults to TRUE.
   *
   * @return $this
   */
  public function overridePreprocess($status = TRUE): static {
    $this->overridePreprocessFunctions = (bool) $status;

    return $this;
  }

  /**
   * Returns whether the list of preprocess functions is incomplete.
   *
   * Generally this is for internal use only. A completely built registry should
   * contain only completed theme hooks.
   *
   * @return bool
   *   TRUE if the list of preprocess functions is incomplete.
   */
  public function isIncomplete(): bool {
    return $this->incompletePreprocessFunctions;
  }

  /**
   * Marks this theme hook as being incomplete.
   *
   * @see self::isIncomplete()
   *
   * @return $this
   */
  public function markIncomplete(): static {
    $this->incompletePreprocessFunctions = TRUE;

    return $this;
  }

  /**
   * Marks this theme hook as being complete.
   *
   * @see self::isIncomplete()
   *
   * @return $this
   */
  public function markComplete(): static {
    $this->incompletePreprocessFunctions = FALSE;

    return $this;
  }

  /**
   * Gets the name of the theme hook.
   *
   * @return string
   *   The machine name of the theme hook.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Process a theme hook.
   *
   * This is intended for usage by the theme registry.
   *
   * @param string $root
   *   The app root.
   * @param string $theme
   *   The theme currently being processed.
   * @param array $module_list
   *   An array of module names.
   * @param \Drupal\Core\Theme\ThemeHook|null $existing_theme_hook
   *   An existing theme hook, if it exists.
   *
   * @return static
   */
  public function process($root, $theme, array $module_list, ?ThemeHook $existing_theme_hook = NULL): static {
    $hook = $this->getName();

    // If a theme hook has a base hook, mark its preprocess functions always
    // incomplete in order to inherit the base hook's preprocess functions.
    if ($this->getBaseHook()) {
      $this->markIncomplete();
    }

    $this->handleIncludes($root, $existing_theme_hook);

    // Provide a default naming convention for 'template' based on the hook
    // used. If the template does not exist, the theme engine used should throw
    // an exception at runtime when attempting to include the template file.
    if (!$this->getTemplate()) {
      $this->setTemplate(strtr($hook, '_', '-'));
    }

    // Prepend the current theming path when none is set. This is required for
    // the default theme engine to know where the template lives.
    if ($this->getTemplate() && !$this->getPath()) {
      $this->setPath($this->getThemePath() . '/templates');
    }

    if ($existing_theme_hook) {
      $this->handleDefaultValues($existing_theme_hook);
    }
    $this->handlePreprocess($theme, $module_list);

    // Merge the newly created theme hooks into the existing cache.
    if ($existing_theme_hook) {
      $result = $this->merge($existing_theme_hook);
    }
    else {
      $result = $this;
    }

    if (!$result->getRenderElement() && !$result->hasVariables() && !$result->getBaseHook()) {
      // @todo Convert this to an exception in https://www.drupal.org/i/2873117.
      @trigger_error(sprintf('Creating a "%s" theme hook without either a render element, variables, or a base hook is deprecated in drupal:11.1.0 and will throw exception in drupal:12.0.0. Define one of them. See https://www.drupal.org/node/2873756', $result->getName()), E_USER_DEPRECATED);
    }
    return $result;
  }

  /**
   * Handles including any files for the processing of this theme hook.
   *
   * @param string $root
   *   The app root.
   * @param \Drupal\Core\Theme\ThemeHook|null $existing_theme_hook
   *   An existing theme hook, if it exists.
   */
  protected function handleIncludes($root, ?ThemeHook $existing_theme_hook = NULL): void {
    if ($existing_theme_hook && $existing_theme_hook->hasIncludes()) {
      $this->setIncludes($existing_theme_hook->getIncludes());
    }

    // If the theme implementation defines a file, then also use the path
    // that it defined. Otherwise use the default path. This allows
    // system.module to declare theme functions on behalf of core .include
    // files.
    if ($this->getFile()) {
      $include_file = $this->getPath() ?: $this->getThemePath();
      $include_file .= '/' . $this->getFile();
      $this->addInclude($include_file);
    }

    // Load the includes, as they may contain preprocess functions.
    if ($this->hasIncludes()) {
      foreach ($this->getIncludes() as $include_file) {
        include_once $root . '/' . $include_file;
      }
    }
  }

  /**
   * Handles merging in any default values from an existing theme hook.
   *
   * @param \Drupal\Core\Theme\ThemeHook $existing_theme_hook
   *   An existing theme hook.
   */
  protected function handleDefaultValues(ThemeHook $existing_theme_hook): void {
    if ($existing_theme_hook->hasVariables() && !$this->hasVariables()) {
      $this->setVariables($existing_theme_hook->getVariables());
    }
    if (!$this->getPattern()) {
      $this->setPattern($existing_theme_hook->getPattern());
    }
    if (!$this->getBaseHook()) {
      $this->setBaseHook($existing_theme_hook->getBaseHook());
    }
    if ($existing_theme_hook->getRenderElement() && !$this->getRenderElement()) {
      $this->setRenderElement($existing_theme_hook->getRenderElement());
    }
  }

  /**
   * Generates the list of preprocess functions from the theme and module list.
   *
   * @param string $theme
   *   The theme currently being processed.
   * @param array $module_list
   *   An array of module names.
   */
  protected function handlePreprocess($theme, array $module_list): void {
    // Preprocess variables for all theming hooks. Ensure they are arrays.
    if (!$this->getPreprocessFunctions()) {
      $provider_name = $this->getProvider();
      $type = $this->getProviderType();
      $hook = $this->getName();

      $prefixes = [];
      if ($type == 'module') {
        // Default variable preprocessor prefix.
        $prefixes[] = 'template';
        // Add all modules so they can intervene with their own variable
        // preprocessors. This allows them to provide variable preprocessors
        // even if they are not the owner of the current hook.
        $prefixes = array_merge($prefixes, $module_list);
      }
      elseif ($type == 'theme_engine' || $type == 'base_theme_engine') {
        // Theme engines get an extra set that come before the normally
        // named variable preprocessors.
        $prefixes[] = $provider_name . '_engine';
        // The theme engine registers on behalf of the theme using the
        // theme's name.
        $prefixes[] = $theme;
      }
      else {
        // This applies when the theme manually registers their own variable
        // preprocessors.
        $prefixes[] = $provider_name;
      }
      foreach ($prefixes as $prefix) {
        // Only use non-hook-specific variable preprocessors for theming
        // hooks implemented as templates. See the @defgroup themeable
        // topic.
        if ($this->getTemplate() && function_exists($prefix . '_preprocess')) {
          $this->addPreprocessFunction($prefix . '_preprocess');
        }
        if (function_exists($prefix . '_preprocess_' . $hook)) {
          $this->addPreprocessFunction($prefix . '_preprocess_' . $hook);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $values = get_object_vars($this);

    // In order to minimize the registry, exclude any key with default values.
    $default_properties = (new \ReflectionClass($this))->getDefaultProperties();
    foreach ($values as $key => $value) {
      if (array_key_exists($key, $default_properties) && $default_properties[$key] === $value) {
        unset($values[$key]);
      }
    }

    return array_keys($values);
  }

}
