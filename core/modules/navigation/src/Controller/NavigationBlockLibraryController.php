<?php

namespace Drupal\navigation\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Menu\LocalActionManagerInterface;
use Drupal\Core\Plugin\Context\LazyContextRepository;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\navigation\NavigationBlockManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a list of navigation block plugins to be added to the layout.
 */
class NavigationBlockLibraryController extends ControllerBase {

  /**
   * The navigation block manager.
   *
   * @var \Drupal\navigation\NavigationBlockManagerInterface
   */
  protected $navigationBlockManager;

  /**
   * The context repository.
   *
   * @var \Drupal\Core\Plugin\Context\LazyContextRepository
   */
  protected $contextRepository;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The local action manager.
   *
   * @var \Drupal\Core\Menu\LocalActionManagerInterface
   */
  protected $localActionManager;

  /**
   * Constructs a NavigationBlockLibraryController object.
   *
   * @param \Drupal\navigation\NavigationBlockManagerInterface $navigation_block_manager
   *   The navigation block manager.
   * @param \Drupal\Core\Plugin\Context\LazyContextRepository $context_repository
   *   The context repository.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Menu\LocalActionManagerInterface $local_action_manager
   *   The local action manager.
   */
  public function __construct(NavigationBlockManagerInterface $navigation_block_manager, LazyContextRepository $context_repository, RouteMatchInterface $route_match, LocalActionManagerInterface $local_action_manager) {
    $this->navigationBlockManager = $navigation_block_manager;
    $this->routeMatch = $route_match;
    $this->localActionManager = $local_action_manager;
    $this->contextRepository = $context_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.navigation_block'),
      $container->get('context.repository'),
      $container->get('current_route_match'),
      $container->get('plugin.manager.menu.local_action')
    );
  }

  /**
   * Shows a list of navigation blocks that can be added.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   A render array as expected by the renderer.
   */
  public function listNavigationBlocks(Request $request) {
    // Since modals do not render any other part of the page, we need to render
    // them manually as part of this listing.
    if ($request->query->get(MainContentViewSubscriber::WRAPPER_FORMAT) === 'drupal_modal') {
      $build['local_actions'] = $this->buildLocalActions();
    }

    $headers = [
      ['data' => $this->t('Navigation block')],
      ['data' => $this->t('Category')],
      ['data' => $this->t('Operations')],
    ];

    $region = $request->query->get('region');
    $weight = $request->query->get('weight');

    // Only add blocks which work without any available context.
    $definitions = $this->navigationBlockManager->getFilteredDefinitions('block_ui', $this->contextRepository->getAvailableContexts(), [
      'region' => $region,
    ]);
    // Order by category, and then by admin label.
    $definitions = $this->navigationBlockManager->getSortedDefinitions($definitions);

    $rows = [];
    foreach ($definitions as $plugin_id => $plugin_definition) {
      $row = [];
      $row['title']['data'] = [
        '#prefix' => '<div class="block-filter-text-source">',
        '#suffix' => '</div>',
        '#plain_text' => $plugin_definition['admin_label'],
      ];
      $row['category']['data'] = [
        '#plain_text' => $plugin_definition['category'],
      ];
      $links['add'] = [
        'title' => $this->t('Place navigation block'),
        'url' => Url::fromRoute('navigation_block.admin_add', ['plugin_id' => $plugin_id]),
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 970,
          ]),
        ],
      ];
      if ($region) {
        $links['add']['query']['region'] = $region;
      }
      if (isset($weight)) {
        $links['add']['query']['weight'] = $weight;
      }
      $row['operations']['data'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];
      $rows[] = $row;
    }

    $build['#attached']['library'][] = 'navigation/navigation_block.admin';

    $build['filter'] = [
      '#type' => 'search',
      '#title' => $this->t('Filter'),
      '#title_display' => 'invisible',
      '#size' => 30,
      '#placeholder' => $this->t('Filter by navigation block name'),
      '#attributes' => [
        'class' => ['table-filter-text'],
        'data-table' => '.navigation-block-add-table',
        'data-items' => 'tbody tr',
        'data-singular' => 'navigation block',
        'data-plural' => 'navigation blocks',
        'title' => $this->t('Enter a part of the navigation block name to filter by.'),
      ],
    ];

    $build['navigation_blocks'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No navigation blocks available.'),
      '#attributes' => [
        'class' => ['navigation-block-add-table'],
      ],
    ];

    return $build;
  }

  /**
   * Builds the local actions for this listing.
   *
   * @return array
   *   An array of local actions for this listing.
   */
  protected function buildLocalActions() {
    $build = $this->localActionManager->getActionsForRoute($this->routeMatch->getRouteName());
    // Without this workaround, the action links will be rendered as <li> with
    // no wrapping <ul> element.
    if (!empty($build)) {
      $build['#prefix'] = '<ul class="action-links">';
      $build['#suffix'] = '</ul>';
    }
    return $build;
  }

}
