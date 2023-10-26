<?php

namespace Drupal\system;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\Core\Utility\RequestGenerator;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Defines a class to build path-based breadcrumbs.
 *
 * @see \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface
 */
class PathBasedBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * Site config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs the PathBasedBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Routing\RequestContext $context
   *   The router request context.
   * @param \Drupal\Core\Access\AccessManagerInterface $accessManager
   *   The access check service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface|\Symfony\Component\Routing\Matcher\RequestMatcherInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Controller\TitleResolverInterface|\Drupal\Core\PathProcessor\InboundPathProcessorInterface $titleResolver
   *   The title resolver service.
   * @param \Drupal\Core\Session\AccountInterface|\Drupal\Core\Config\ConfigFactoryInterface $currentUser
   *   The current user object.
   * @param \Drupal\Core\Path\PathMatcherInterface|\Drupal\Core\Controller\TitleResolverInterface $pathMatcher
   *   The path matcher service.
   * @param \Drupal\Core\Utility\RequestGenerator $requestGenerator
   *   The request generator.
   */
  public function __construct(
    protected RequestContext $context,
    protected AccessManagerInterface $accessManager,
    ConfigFactoryInterface|RequestMatcherInterface $config_factory,
    protected TitleResolverInterface|InboundPathProcessorInterface $titleResolver,
    protected AccountInterface|ConfigFactoryInterface $currentUser,
    protected PathMatcherInterface|TitleResolverInterface $pathMatcher,
    protected RequestGenerator $requestGenerator,
  ) {
    $this->config = $config_factory->get('system.site');
    if ($config_factory instanceof RequestMatcherInterface){
      @trigger_error('Calling PathBasedBreadcrumbBuilder::__construct() with the $router argument is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. See https://www.drupal.org/node/3370946', E_USER_DEPRECATED);
      $this->config = $this->currentUser;
    }
    if ($this->titleResolver instanceof InboundPathProcessorInterface){
      @trigger_error('Calling PathBasedBreadcrumbBuilder::__construct() with the $path_processor argument is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. See https://www.drupal.org/node/3370946', E_USER_DEPRECATED);
      $this->titleResolver = $this->pathMatcher;
    }
    if ($this->pathMatcher === NULL){
      @trigger_error('Calling PathBasedBreadcrumbBuilder::__construct() without the $pathMatcher argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3370946', E_USER_DEPRECATED);
      $this->pathMatcher = \Drupal::service('path.matcher');
    }
    if ($this->requestGenerator === NULL) {
      @trigger_error('Calling PathBasedBreadcrumbBuilder::__construct() without the $requestGenerator argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3370946', E_USER_DEPRECATED);
      $this->requestGenerator = \Drupal::service('request_generator');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $links = [];

    // Add the url.path.parent cache context. This code ignores the last path
    // part so the result only depends on the path parents.
    $breadcrumb->addCacheContexts(['url.path.parent', 'url.path.is_front']);

    // Do not display a breadcrumb on the frontpage.
    if ($this->pathMatcher->isFrontPage()) {
      return $breadcrumb;
    }

    // General path-based breadcrumbs. Use the actual request path, prior to
    // resolving path aliases, so the breadcrumb can be defined by simply
    // creating a hierarchy of path aliases.
    $path = trim($this->context->getPathInfo(), '/');
    $path_elements = explode('/', $path);
    $exclude = [];
    // Don't show a link to the front-page path.
    $front = $this->config->get('page.front');
    $exclude[$front] = TRUE;
    // /user is just a redirect, so skip it.
    // @todo Find a better way to deal with /user.
    $exclude['/user'] = TRUE;
    while (count($path_elements) > 1) {
      array_pop($path_elements);
      // Copy the path elements for up-casting.
      $route_request = $this->requestGenerator->generateRequestForPath('/' . implode('/', $path_elements), $exclude);
      if ($route_request) {
        $route_match = RouteMatch::createFromRequest($route_request);
        $access = $this->accessManager->check($route_match, $this->currentUser, NULL, TRUE);
        // The set of breadcrumb links depends on the access result, so merge
        // the access result's cacheability metadata.
        $breadcrumb = $breadcrumb->addCacheableDependency($access);
        if ($access->isAllowed()) {
          $title = $this->titleResolver->getTitle($route_request, $route_match->getRouteObject());
          if (!isset($title)) {
            // Fallback to using the raw path component as the title if the
            // route is missing a _title or _title_callback attribute.
            $title = str_replace(['-', '_'], ' ', Unicode::ucfirst(end($path_elements)));
          }
          $url = Url::fromRouteMatch($route_match);
          $links[] = new Link($title, $url);
        }
      }
    }

    // Add the Home link.
    $links[] = Link::createFromRoute($this->t('Home'), '<front>');

    return $breadcrumb->setLinks(array_reverse($links));
  }

}
