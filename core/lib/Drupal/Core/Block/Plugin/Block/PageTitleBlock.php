<?php

namespace Drupal\Core\Block\Plugin\Block;

use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\TitleBlockPluginInterface;
use Drupal\Core\Controller\BaseRouteTitleResolver;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Utility\BaseRouteTitle;
use Drupal\Core\Utility\RequestGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
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
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Controller\BaseRouteTitleResolver $baseRouteTitleResolver
   *   The base route title.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected TitleResolverInterface $titleResolver,
    protected RouteMatchInterface $routeMatch,
    protected RequestStack $requestStack,
    protected BaseRouteTitleResolver $baseRouteTitleResolver,
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
      $container->get('request_stack'),
      $container->get('base_route_title_resolver'),
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
    $title = $this->title;
    if ($this->configuration['contextualize_title']) {
      $contextualized_title = $this->getTitleBasedOnBaseRoute();
      if (!is_null($contextualized_title)) {
        $title = $contextualized_title;
      }
    }
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
   * Gets title based on base route.
   *
   * @return array|string|\Stringable|null
   *   The title based on base route.
   */
  private function getTitleBasedOnBaseRoute(): array|string|null|\Stringable {
    $base_route_title = $this->baseRouteTitleResolver->getTitle(\Drupal::requestStack()->getCurrentRequest(), \Drupal::routeMatch()->getRouteObject());
    if (!is_null($base_route_title)) {
      if (is_array($base_route_title)) {
        $base_route_title = \Drupal::service('renderer')->render($base_route_title);
      }
      $current_title = $this->title;
      if (is_array($current_title)) {
        $current_title = \Drupal::service('renderer')->render($current_title);
      }

      // If the titles are equal, return the original title.
      if ((string) $base_route_title === (string) $current_title) {
        return $this->title;
      }

      return $this->t('@section_title<span class="visually-hidden">: @current_title</span>', [
        '@section_title' => $base_route_title,
        '@current_title' => $current_title,
      ]);
    }
    return NULL;
  }

}
