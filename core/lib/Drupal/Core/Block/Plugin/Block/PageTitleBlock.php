<?php

namespace Drupal\Core\Block\Plugin\Block;

use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\TitleBlockPluginInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\LocalTaskManager;
use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a block to display the page title.
 */
#[Block(
  id: "page_title_block",
  admin_label: new TranslatableMarkup("Page title"),
  forms: [
    'settings_tray' => FALSE,
  ]
)]
class PageTitleBlock extends BlockBase implements TitleBlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The page title: a string (plain title) or a render array (formatted title).
   *
   * @var string|array
   */
  protected $title = '';

  /**
   * Constructs a new PageTitleBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Controller\TitleResolverInterface $titleResolver
   *   The title resolver.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   * @param \Drupal\Core\Menu\LocalTaskManager $localTaskManager
   *   The local task manager.
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The route provider.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\PathProcessor\InboundPathProcessorInterface $pathProcessor
   *   The inbound path processor.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   The current path.
   * @param \Symfony\Component\Routing\Matcher\RequestMatcherInterface $router
   *   The dynamic router service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected TitleResolverInterface $titleResolver,
    protected RouteMatchInterface $routeMatch,
    protected LocalTaskManager $localTaskManager,
    protected RouteProviderInterface $routeProvider,
    protected RequestStack $requestStack,
    protected InboundPathProcessorInterface $pathProcessor,
    protected CurrentPathStack $currentPath,
    protected RequestMatcherInterface $router,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('title_resolver'),
      $container->get('current_route_match'),
      $container->get('plugin.manager.menu.local_task'),
      $container->get('router.route_provider'),
      $container->get('request_stack'),
      $container->get('path_processor_manager'),
      $container->get('path.current'),
      $container->get('router.no_access_checks'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => FALSE,
      'contextualize_title' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($this->configuration['contextualize_title']) {
      $this->setConfigurationForTitle();
    }
    $title = $this->configuration['title_when_base_route_is_available'] ?? $this->title;
    return [
      '#type' => 'page_title',
      '#title' => $title,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['contextualize_title'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Contextualize title based on page hierarchy'),
      '#default_value' => $this->configuration['contextualize_title'],
      '#description' => $this->t('Display the page title based on the current context opposed to the default behaviour which displays title based on the current page.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['contextualize_title'] = $form_state->getValue('contextualize_title');
  }

  /**
   * Sets configuration for title when base route is available.
   */
  private function setConfigurationForTitle() {
    $route_name = $this->routeMatch->getRouteName();
    $base_route = $this->localTaskManager->getBaseRoute($route_name);
    $title = NULL;
    if ($base_route) {
      if ($base_route !== $route_name) {
        $path = \Drupal::service('url_generator')->getPathFromRoute($base_route, $this->routeMatch->getRawParameters()->all());
        $route_request = $this->getRequestForPath($path);
        $title = $this->titleResolver->getTitle($route_request, $this->routeProvider->getRouteByName($base_route));
      }
      else {
        $title = $this->titleResolver->getTitle($this->requestStack->getCurrentRequest(), $this->routeMatch->getRouteObject());
      }
      if (is_string($title)) {
        $title = $this->t($title);
      }
    }
    if ($title) {
      $this->configuration['title_when_base_route_is_available'] = $title;
    }
  }

  /**
   * Matches a path in the router.
   *
   * @param string $path
   *   The request path with a leading slash.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   A populated request object or NULL if the path couldn't be matched.
   */
  private function getRequestForPath(string $path) {
    $request = Request::create($path);
    // Performance optimization: set a short accept header to reduce overhead in
    // AcceptHeaderMatcher when matching the request.
    $request->headers->set('Accept', 'text/html');
    // Find the system path by resolving aliases, language prefix, etc.
    $processed = $this->pathProcessor->processInbound($path, $request);
    if (empty($processed)) {
      // This resolves to the front page, which we already add.
      return NULL;
    }
    $this->currentPath->setPath($processed, $request);
    // Attempt to match this path to provide a fully built request.
    try {
      $request->attributes->add($this->router->matchRequest($request));
      return $request;
    }
    catch (ParamNotConvertedException | ResourceNotFoundException | MethodNotAllowedException | AccessDeniedHttpException $e) {
      return NULL;
    }
  }

}
