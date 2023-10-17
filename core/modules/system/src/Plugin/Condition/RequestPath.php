<?php

namespace Drupal\system\Plugin\Condition;

use Drupal\Core\Condition\Attribute\Condition;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a 'Request Path' condition.
 */
#[Condition(
  id: "request_path",
  label: new TranslatableMarkup("Request Path"),
)]
class RequestPath extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Constructs a RequestPath condition plugin.
   *
   * @param \Drupal\Core\Path\PathMatcherInterface|\Drupal\path_alias\AliasManagerInterface $path_matcher
   *   The path matcher service.
   * @param \Symfony\Component\HttpFoundation\RequestStack|\Drupal\Core\Path\PathMatcherInterface $request_stack
   *   The request stack.
   * @param \Drupal\Core\Path\CurrentPathStack|\Symfony\Component\HttpFoundation\RequestStack $current_path
   *   The current path.
   * @param array|\Drupal\Core\Path\CurrentPathStack $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string|array $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array|string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(PathMatcherInterface|AliasManagerInterface $path_matcher, RequestStack|PathMatcherInterface $request_stack, CurrentPathStack|RequestStack $current_path, array|CurrentPathStack $configuration, $plugin_id, array|string $plugin_definition) {
    if ($path_matcher instanceof AliasManagerInterface) {
      @trigger_error('Calling ' . __CLASS__ . '::__construct() with the $alias_manager argument is deprecated in drupal:10.2.0 and is removed in drupal:11.0.0. See https://www.drupal.org/node/3096092', E_USER_DEPRECATED);
      $path_matcher = func_get_arg(1);
      $request_stack = func_get_arg(2);
      $current_path = func_get_arg(3);
      $configuration = func_get_arg(4);
      $plugin_id = func_get_arg(5);
      $plugin_definition = func_get_arg(6);
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pathMatcher = $path_matcher;
    $this->requestStack = $request_stack;
    $this->currentPath = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('path.matcher'),
      $container->get('request_stack'),
      $container->get('path.current'),
      $configuration,
      $plugin_id,
      $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['pages' => ''] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#default_value' => $this->configuration['pages'],
      '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. An example path is %user-wildcard for every user page. %front is the front page.", [
        '%user-wildcard' => '/user/*',
        '%front' => '<front>',
      ]),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $paths = array_map('trim', explode("\n", $form_state->getValue('pages')));
    foreach ($paths as $path) {
      if (empty($path) || $path === '<front>' || str_starts_with($path, '/')) {
        continue;
      }
      $form_state->setErrorByName('pages', $this->t("The path %path requires a leading forward slash when used with the Pages setting.", ['%path' => $path]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['pages'] = $form_state->getValue('pages');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (empty($this->configuration['pages'])) {
      return $this->t('No page is specified');
    }
    $pages = array_map('trim', explode("\n", $this->configuration['pages']));
    $pages = implode(', ', $pages);
    if (!empty($this->configuration['negate'])) {
      return $this->t('Do not return true on the following pages: @pages', ['@pages' => $pages]);
    }
    return $this->t('Return true on the following pages: @pages', ['@pages' => $pages]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    // Convert path to lowercase. This allows comparison of the same path
    // with different case. Ex: /Page, /page, /PAGE.
    $pages = mb_strtolower($this->configuration['pages']);
    if (!$pages) {
      return TRUE;
    }

    $request = $this->requestStack->getCurrentRequest();
    $path = $this->currentPath->getPath($request);
    // Do not trim a trailing slash if that is the complete path.
    $path = $path === '/' ? $path : rtrim($path, '/');

    return $this->pathMatcher->matchPath($path, $pages);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();
    $contexts[] = 'url.path';
    return $contexts;
  }

}
